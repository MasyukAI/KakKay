<?php

declare(strict_types=1);

namespace App\Livewire\Checkout;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

final class OrderSummaryStep extends Component
{
    public array $checkoutData;

    public ?Order $order = null;

    public bool $orderProcessed = false;

    public string $orderNumber = '';

    public function mount(array $checkoutData = []): void
    {
        $this->checkoutData = $checkoutData;

        if (! $this->orderProcessed && ! empty($checkoutData)) {
            $this->processOrder();
        }
    }

    public function processOrder(): void
    {
        try {
            DB::beginTransaction();

            // Create the order
            $this->order = Order::create([
                'user_id' => Auth::id(),
                'total_amount' => $this->getTotal(),
                'status' => 'pending',
                'payment_status' => 'pending',
                'shipping_address' => json_encode($this->checkoutData['payment']['delivery'] ?? []),
                'payment_method' => $this->checkoutData['payment']['payment_method'] ?? 'credit-card',
                'delivery_method' => $this->checkoutData['payment']['delivery_method'] ?? 'dhl',
            ]);

            // Create order items
            foreach ($this->checkoutData['cart']['items'] ?? [] as $item) {
                OrderItem::create([
                    'order_id' => $this->order->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'], // Use unit_price as per migration
                ]);
            }

            // Create payment record
            Payment::create([
                'order_id' => $this->order->id,
                'amount' => $this->getTotal(),
                'method' => $this->checkoutData['payment']['payment_method'] ?? 'credit-card',
                'status' => 'pending',
            ]);

            $this->orderNumber = 'ORD-'.mb_str_pad($this->order->id, 6, '0', STR_PAD_LEFT);
            $this->orderProcessed = true;

            DB::commit();

            // Dispatch success event
            $this->dispatch('order-created', ['order_id' => $this->order->id]);

        } catch (Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to process your order. Please try again.');
            $this->dispatch('previous-step');
        }
    }

    public function goBack(): void
    {
        $this->dispatch('previous-step');
    }

    public function startNewOrder(): void
    {
        $this->dispatch('go-to-step', 1);
    }

    public function getSubtotal(): int
    {
        $subtotal = 0;
        foreach ($this->checkoutData['cart']['items'] ?? [] as $item) {
            $subtotal += ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
        }

        return $subtotal;
    }

    public function getSavings(): int
    {
        return $this->checkoutData['cart']['savings'] ?? 0;
    }

    public function getDeliveryFee(): int
    {
        $method = $this->checkoutData['payment']['delivery_method'] ?? 'standard';

        return match ($method) {
            'express' => 4900, // RM49
            'fast' => 1500,    // RM15
            'pickup' => 0,     // Free pickup
            'standard' => 500, // RM5 Standard shipping
            default => 500     // Default to standard shipping
        };
    }

    public function getPaymentFee(): int
    {
        $method = $this->checkoutData['payment']['payment_method'] ?? 'credit-card';

        return match ($method) {
            'pay-on-delivery' => 300, // RM3 fee for COD
            default => 0
        };
    }

    public function getTax(): int
    {
        return (int) ($this->getSubtotal() * 0.1); // 10% tax
    }

    public function getTotal(): int
    {
        return $this->getSubtotal()
            + $this->getDeliveryFee()
            + $this->getPaymentFee()
            + $this->getTax();
    }

    public function formatPrice(int $cents): string
    {
        return 'RM '.number_format($cents / 100, 2);
    }

    public function getDeliveryMethodName(): string
    {
        $method = $this->checkoutData['payment']['delivery_method'] ?? 'standard';

        return match ($method) {
            'express' => 'Express Delivery (Same Day)',
            'fast' => 'Fast Delivery (Next Day)',
            'pickup' => 'Store Pickup',
            'standard' => 'Standard Delivery (3-5 days)',
            default => 'Standard Delivery (3-5 days)'
        };
    }

    public function getPaymentMethodName(): string
    {
        $method = $this->checkoutData['payment']['payment_method'] ?? 'credit-card';

        return match ($method) {
            'credit-card' => 'Credit Card',
            'pay-on-delivery' => 'Payment on Delivery',
            'paypal' => 'PayPal',
            default => 'Credit Card'
        };
    }

    public function render()
    {
        return view('livewire.checkout.order-summary-step');
    }
}
