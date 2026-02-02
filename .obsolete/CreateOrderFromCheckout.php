<?php

declare(strict_types=1);

namespace App\Listeners;

use AIArmada\Cart\Contracts\CartManagerInterface;
use AIArmada\Checkout\Events\PaymentCompleted;
use AIArmada\Checkout\Models\CheckoutSession;
use AIArmada\Checkout\States\Completed;
use App\Events\OrderPaid;
use App\Models\Address;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Creates an order after payment is confirmed.
 *
 * This listener bridges the aiarmada/checkout package payment flow
 * with the app's existing order creation logic.
 */
final class CreateOrderFromCheckout
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly CartManagerInterface $cartManager,
    ) {}

    public function handle(PaymentCompleted $event): void
    {
        $session = $event->session;

        if ($session->order_id !== null) {
            Log::debug('Order already exists for checkout session', [
                'session_id' => $session->id,
                'order_id' => $session->order_id,
            ]);

            return;
        }

        Log::info('Creating order from confirmed payment', [
            'session_id' => $session->id,
            'payment_id' => $session->payment_id,
        ]);

        try {
            [$order, $payment] = DB::transaction(function () use ($session): array {
                $user = $this->resolveUser($session);
                $address = $this->createAddress($user, $session);

                $cartSnapshot = $session->cart_snapshot ?? [];
                $cartItems = $cartSnapshot['items'] ?? [];

                $order = $this->orderService->createOrder(
                    $user,
                    $address,
                    $this->buildCustomerData($session),
                    $cartItems,
                    $cartSnapshot
                );

                $payment = $this->createPayment($order, $session);

                $order = $this->orderService->updateOrderStatus($order, 'completed');

                $session->update([
                    'order_id' => $order->id,
                    'completed_at' => now(),
                ]);
                $session->status->transitionTo(Completed::class);

                return [$order, $payment];
            });

            OrderPaid::dispatch($order, $payment, [
                'checkout_session_id' => $session->id,
                'source' => 'checkout_package',
            ], 'payment_completed');

            $this->clearCart($session);

            Log::info('Order created from confirmed payment', [
                'session_id' => $session->id,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);

        } catch (Throwable $e) {
            Log::error('Failed to create order from checkout session', [
                'session_id' => $session->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    private function resolveUser(CheckoutSession $session): User
    {
        $billingData = $session->billing_data ?? [];
        $shippingData = $session->shipping_data ?? [];
        $email = $billingData['email'] ?? $shippingData['email'] ?? null;

        if ($email === null) {
            throw new RuntimeException('Email is required to create order');
        }

        $user = User::where('email', $email)->first();

        if ($user === null) {
            $user = User::create([
                'name' => $billingData['name'] ?? $shippingData['name'] ?? null,
                'email' => $email,
                'phone' => $billingData['phone'] ?? $shippingData['phone'] ?? null,
                'is_guest' => true,
            ]);
        }

        return $user;
    }

    private function createAddress(User $user, CheckoutSession $session): Address
    {
        $shippingData = $session->shipping_data ?? [];
        $billingData = $session->billing_data ?? [];

        $addressData = array_merge($billingData, $shippingData);

        return Address::create([
            'addressable_type' => User::class,
            'addressable_id' => $user->id,
            'name' => $addressData['name'] ?? null,
            'company' => $addressData['company'] ?? null,
            'street1' => $addressData['street1'] ?? $addressData['line1'] ?? null,
            'street2' => $addressData['street2'] ?? $addressData['line2'] ?? null,
            'city' => $addressData['city'] ?? null,
            'state' => $addressData['state'] ?? null,
            'country' => $addressData['country'] ?? $addressData['country_code'] ?? 'Malaysia',
            'postcode' => $addressData['postcode'] ?? null,
            'phone' => $addressData['phone'] ?? null,
            'type' => 'shipping',
            'is_primary' => true,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildCustomerData(CheckoutSession $session): array
    {
        $billingData = $session->billing_data ?? [];
        $shippingData = $session->shipping_data ?? [];

        return array_merge($billingData, $shippingData, [
            'reference' => $session->cart_id,
        ]);
    }

    private function createPayment(Order $order, CheckoutSession $session): Payment
    {
        $paymentData = $session->payment_data ?? [];

        return Payment::create([
            'order_id' => $order->id,
            'amount' => $order->total,
            'status' => 'completed',
            'method' => $paymentData['gateway'] ?? 'chip',
            'currency' => $session->currency ?? 'MYR',
            'gateway_payment_id' => $session->payment_id,
            'gateway_transaction_id' => $paymentData['transaction_id'] ?? null,
            'gateway_response' => $paymentData,
            'reference' => $session->cart_id,
            'paid_at' => now(),
        ]);
    }

    private function clearCart(CheckoutSession $session): void
    {
        try {
            $cart = $this->cartManager->getById($session->cart_id);

            if ($cart !== null) {
                $cart->clear();

                Log::debug('Cart cleared after order creation', [
                    'cart_id' => $session->cart_id,
                ]);
            }
        } catch (Throwable $e) {
            Log::warning('Failed to clear cart after order creation', [
                'cart_id' => $session->cart_id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
