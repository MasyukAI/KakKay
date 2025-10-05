<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\CheckoutService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use MasyukAI\Chip\Events\PurchaseCreated;
use MasyukAI\Chip\Events\PurchasePaid;
use MasyukAI\Chip\Services\WebhookService;

final class ChipWebhookController extends Controller
{
    public function __construct(
        protected WebhookService $webhookService,
        protected CheckoutService $checkoutService
    ) {
        //
    }

    /**
     * Handle CHIP webhook
     */
    public function handle(Request $request): Response
    {
        try {
            // Verify signature
            if (! $this->webhookService->verifySignature($request)) {
                Log::error('CHIP webhook signature verification failed');

                return response('Unauthorized', 401);
            }

            $eventType = $request->input('event');
            $purchaseData = $request->input('data', []);

            Log::info('CHIP webhook received', [
                'event' => $eventType,
                'purchase_id' => $purchaseData['id'] ?? null,
            ]);

            // Handle different webhook events
            switch ($eventType) {
                case 'purchase.paid':
                    $this->handlePurchasePaid($purchaseData);
                    break;

                case 'purchase.created':
                    $this->handlePurchaseCreated($purchaseData);
                    break;

                case 'purchase.cancelled':
                    $this->handlePurchaseCancelled($purchaseData);
                    break;

                case 'purchase.refunded':
                    $this->handlePurchaseRefunded($purchaseData);
                    break;

                default:
                    Log::info('Unhandled CHIP webhook event', [
                        'event' => $eventType,
                        'data' => $purchaseData,
                    ]);
            }

            return response('OK', 200);
        } catch (Exception $e) {
            Log::error('CHIP webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response('Internal Server Error', 500);
        }
    }

    /**
     * Handle purchase paid webhook
     */
    protected function handlePurchasePaid(array $purchaseData): void
    {
        $purchaseId = $purchaseData['id'];

        Log::info('Processing purchase paid webhook', [
            'purchase_id' => $purchaseId,
            'amount' => $purchaseData['amount'] ?? null,
        ]);

        // Try to create order from cart payment intent first
        $order = $this->checkoutService->handlePaymentSuccess($purchaseId, $purchaseData);

        if ($order) {
            Log::info('Order created successfully from cart payment intent', [
                'order_id' => $order->id,
                'purchase_id' => $purchaseId,
            ]);

            // Dispatch payment success event
            event(new PurchasePaid($order->payments()->first(), $purchaseData));

            return;
        }

        // Fallback: Try to find existing payment record (for backward compatibility)
        $this->handleExistingPaymentRecord($purchaseData);
    }

    /**
     * Handle existing payment record update (backward compatibility)
     */
    protected function handleExistingPaymentRecord(array $purchaseData): void
    {
        $purchaseId = $purchaseData['id'];
        $reference = $purchaseData['reference'] ?? null;

        // Find payment by gateway payment ID
        $payment = Payment::where('gateway_payment_id', $purchaseId)->first();

        if (! $payment && $reference) {
            // Try to find by order reference
            $order = Order::where('order_number', $reference)->first();
            if ($order) {
                $payment = $order->payments()->where('gateway_payment_id', $purchaseId)->first();
            }
        }

        if ($payment && $payment->order) {
            // Update payment status
            $payment->update([
                'status' => 'completed',
                'gateway_transaction_id' => $purchaseData['payment']['id'] ?? null,
                'gateway_response' => $purchaseData,
                'method' => $purchaseData['transaction_data']['payment_method'] ?? 'chip',
                'paid_at' => now(),
            ]);

            // Update order status
            $payment->order->update([
                'status' => 'processing', // Order is paid and being processed
            ]);

            Log::info('Payment updated successfully', [
                'payment_id' => $payment->id,
                'order_id' => $payment->order->id,
                'purchase_id' => $purchaseId,
            ]);

            // Dispatch payment success event
            event(new PurchasePaid($payment, $purchaseData));
        } else {
            Log::warning('Payment or order not found for completed purchase', [
                'purchase_id' => $purchaseId,
                'reference' => $reference,
            ]);
        }
    }

    /**
     * Handle purchase created webhook
     */
    protected function handlePurchaseCreated(array $purchaseData): void
    {
        $purchaseId = $purchaseData['id'];

        Log::info('Processing purchase created webhook', [
            'purchase_id' => $purchaseId,
        ]);

        // Dispatch event
        if (class_exists(PurchaseCreated::class)) {
            $purchase = \Masyukai\Chip\DataObjects\Purchase::fromArray($purchaseData);
            event(new PurchaseCreated($purchase));
        }
    }

    /**
     * Handle purchase cancelled webhook
     */
    protected function handlePurchaseCancelled(array $purchaseData): void
    {
        $purchaseId = $purchaseData['id'];

        Log::info('Processing purchase cancelled webhook', [
            'purchase_id' => $purchaseId,
        ]);

        $payment = Payment::where('gateway_payment_id', $purchaseId)->first();

        if ($payment && $payment->order) {
            // Update payment status
            $payment->update([
                'status' => 'cancelled',
                'gateway_response' => $purchaseData,
                'failed_at' => now(),
            ]);

            // Update order status
            $payment->order->update([
                'status' => 'cancelled',
            ]);

            // Add status history
            $payment->order->statusHistories()->create([
                'from_status' => $payment->order->status,
                'to_status' => 'cancelled',
                'actor_type' => 'gateway',
                'meta' => [
                    'gateway' => 'chip',
                    'purchase_id' => $purchaseId,
                    'payment_id' => $payment->id,
                ],
                'note' => 'Payment cancelled via CHIP webhook',
                'changed_at' => now(),
            ]);

            Log::info('Payment and order cancelled', [
                'order_id' => $payment->order->id,
                'payment_id' => $payment->id,
                'purchase_id' => $purchaseId,
            ]);
        }
    }

    /**
     * Handle purchase refunded webhook
     */
    protected function handlePurchaseRefunded(array $purchaseData): void
    {
        $purchaseId = $purchaseData['id'];

        Log::info('Processing purchase refunded webhook', [
            'purchase_id' => $purchaseId,
        ]);

        $payment = Payment::where('gateway_payment_id', $purchaseId)->first();

        if ($payment && $payment->order) {
            // Update payment status
            $payment->update([
                'status' => 'refunded',
                'gateway_response' => $purchaseData,
                'refunded_at' => now(),
            ]);

            // Update order status
            $payment->order->update([
                'status' => 'refunded',
            ]);

            // Add status history
            $payment->order->statusHistories()->create([
                'from_status' => $payment->order->status,
                'to_status' => 'refunded',
                'actor_type' => 'gateway',
                'meta' => [
                    'gateway' => 'chip',
                    'purchase_id' => $purchaseId,
                    'payment_id' => $payment->id,
                ],
                'note' => 'Payment refunded via CHIP webhook',
                'changed_at' => now(),
            ]);

            Log::info('Payment and order refunded', [
                'order_id' => $payment->order->id,
                'payment_id' => $payment->id,
                'purchase_id' => $purchaseId,
            ]);
        }
    }
}
