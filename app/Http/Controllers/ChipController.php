<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Notifications\OrderCreationFailed;
use App\Notifications\WebhookProcessingFailed;
use App\Services\CheckoutService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use MasyukAI\Chip\Events\PurchaseCreated;
use MasyukAI\Chip\Events\PurchasePaid;
use MasyukAI\Chip\Services\WebhookService;

final class ChipController extends Controller
{
    public function __construct(
        protected WebhookService $webhookService,
        protected CheckoutService $checkoutService
    ) {
        //
    }

    /**
     * Handle CHIP callbacks (success + webhook events).
     */
    public function handle(Request $request, ?string $webhookId = null): Response
    {
        $webhookId = $webhookId ?: null;

        Log::debug('CHIP webhook entry', [
            'webhook_id' => $webhookId,
            'headers' => $request->headers->all(),
        ]);

        return $this->processWebhook($request, $webhookId);
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

        Log::debug('Invoking checkout service from webhook', [
            'purchase_id' => $purchaseId,
            'payload_keys' => array_keys($purchaseData),
        ]);

        // Try to create order from cart payment intent first
        $orderPayload = $purchaseData + ['source' => 'webhook'];

        $order = $this->checkoutService->handlePaymentSuccess($purchaseId, $orderPayload);

        if ($order) {
            Log::info('Order created successfully from cart payment intent', [
                'order_id' => $order->id,
                'purchase_id' => $purchaseId,
            ]);

            Log::debug('Order creation via webhook succeeded', [
                'order_id' => $order->id,
                'purchase_id' => $purchaseId,
                'payment_count' => $order->payments()->count(),
            ]);

            // Fetch the full purchase from CHIP and dispatch event
            try {
                $purchase = \MasyukAI\Chip\Facades\Chip::getPurchase($purchaseId);
                event(new PurchasePaid($purchase));
            } catch (Exception $e) {
                Log::error('Failed to fetch purchase for event dispatch', [
                    'purchase_id' => $purchaseId,
                    'error' => $e->getMessage(),
                ]);
            }

            return;
        }

        // If order creation failed, notify admin
        Log::warning('Order creation failed from cart payment intent', [
            'purchase_id' => $purchaseId,
            'amount' => $purchaseData['amount'] ?? null,
        ]);

        Notification::route('mail', config('mail.from.address'))
            ->notify(new OrderCreationFailed(
                $purchaseId,
                'Failed to create order from cart payment intent. Check if cart exists or has valid payment intent metadata.',
                $purchaseData
            ));

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

            Log::debug('Fallback payment updated order status', [
                'order_status' => $payment->order->status,
                'payment_status' => $payment->status,
            ]);

            // Dispatch payment success event
            event(new PurchasePaid($payment, $purchaseData));
        } else {
            Log::warning('Payment or order not found for completed purchase', [
                'purchase_id' => $purchaseId,
                'reference' => $reference,
            ]);

            // Notify admin that payment/order could not be found
            Notification::route('mail', config('mail.from.address'))
                ->notify(new OrderCreationFailed(
                    $purchaseId,
                    'Payment or order not found for completed purchase. The payment was successful but no matching payment record exists in the database.',
                    $purchaseData
                ));
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

            Log::debug('Order cancellation persisted', [
                'status_history_count' => $payment->order->statusHistories()->count(),
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

            Log::debug('Order refund persisted', [
                'refunded_at' => $payment->refunded_at,
            ]);
        }
    }

    /**
     * Handle purchase payment failure webhook
     */
    protected function handlePurchasePaymentFailure(array $purchaseData): void
    {
        $purchaseId = $purchaseData['id'] ?? $purchaseData['purchase']['id'] ?? null;

        if (! $purchaseId) {
            Log::warning('Received purchase payment failure webhook without purchase ID', [
                'data' => $purchaseData,
            ]);

            return;
        }

        $failureReason = data_get($purchaseData, 'failure_reason')
            ?? data_get($purchaseData, 'purchase.failure_reason')
            ?? data_get($purchaseData, 'payment.failure_reason')
            ?? data_get($purchaseData, 'transaction_data.failure_reason')
            ?? data_get($purchaseData, 'error_message')
            ?? 'Payment failed at gateway';

        Log::warning('Processing purchase payment failure webhook', [
            'purchase_id' => $purchaseId,
            'reason' => $failureReason,
        ]);

        $payment = Payment::where('gateway_payment_id', $purchaseId)->first();

        if (! $payment) {
            Log::warning('Payment record not found for failed purchase', [
                'purchase_id' => $purchaseId,
            ]);

            return;
        }

        $payment->update([
            'status' => 'failed',
            'gateway_transaction_id' => data_get($purchaseData, 'payment.id'),
            'gateway_response' => $purchaseData,
            'method' => data_get($purchaseData, 'transaction_data.payment_method', $payment->method ?? 'chip'),
            'failed_at' => now(),
            'note' => $failureReason,
        ]);

        $order = $payment->order;

        if ($order) {
            $previousStatus = $order->status;

            $order->update([
                'status' => 'failed',
            ]);

            $order->statusHistories()->create([
                'from_status' => $previousStatus,
                'to_status' => 'failed',
                'actor_type' => 'gateway',
                'meta' => [
                    'gateway' => 'chip',
                    'purchase_id' => $purchaseId,
                    'payment_id' => $payment->id,
                ],
                'note' => 'Payment failed via CHIP webhook: '.$failureReason,
                'changed_at' => now(),
            ]);
        }
    }

    private function processWebhook(Request $request, ?string $webhookId = null): Response
    {
        try {
            $publicKey = $this->webhookService->getPublicKey($webhookId);

            if (! $this->webhookService->verifySignature($request, publicKey: $publicKey)) {
                Log::error('CHIP webhook signature verification failed', [
                    'webhook_id' => $webhookId,
                ]);

                Log::debug('Webhook signature headers', [
                    'available_headers' => $request->headers->all(),
                ]);

                return response('Unauthorized', 401);
            }

            $signatureHash = $publicKey !== ''
                ? mb_substr(hash('sha256', $publicKey), 0, 16)
                : null;

            $invocationMode = $webhookId ? 'webhook' : 'success_callback';

            Log::info('CHIP webhook signature verified', [
                'webhook_id' => $webhookId,
                'mode' => $invocationMode,
                'key_fingerprint' => $signatureHash,
                'signature_header_present' => $request->headers->has('X-Signature'),
            ]);

            $rawPayload = $request->getContent();
            $rawLength = $rawPayload === '' ? 0 : mb_strlen($rawPayload);
            Log::debug('CHIP webhook raw payload captured', [
                'webhook_id' => $webhookId,
                'mode' => $invocationMode,
                'raw_length' => $rawLength,
                'raw_preview' => $rawPayload,
            ]);

            $eventType = $request->input('event');
            $purchaseData = $request->input('data', []);

            Log::debug('Webhook payload extracted', [
                'event' => $eventType,
                'data_keys' => array_keys((array) $purchaseData),
            ]);

            Log::info('CHIP webhook received', [
                'event' => $eventType,
                'purchase_id' => $purchaseData['id'] ?? null,
                'webhook_id' => $webhookId,
            ]);

            // Handle different webhook events
            switch ($eventType) {
                case 'purchase.paid':
                    Log::debug('Dispatching purchase.paid handler', [
                        'purchase_id' => $purchaseData['id'] ?? null,
                    ]);
                    $this->handlePurchasePaid($purchaseData);
                    break;

                case 'purchase.created':
                    Log::debug('Dispatching purchase.created handler', [
                        'purchase_id' => $purchaseData['id'] ?? null,
                    ]);
                    $this->handlePurchaseCreated($purchaseData);
                    break;

                case 'purchase.payment_failure':
                    Log::debug('Dispatching purchase.payment_failure handler', [
                        'purchase_id' => $purchaseData['id'] ?? null,
                    ]);
                    $this->handlePurchasePaymentFailure($purchaseData);
                    break;

                case 'purchase.cancelled':
                    Log::debug('Dispatching purchase.cancelled handler', [
                        'purchase_id' => $purchaseData['id'] ?? null,
                    ]);
                    $this->handlePurchaseCancelled($purchaseData);
                    break;

                case 'purchase.refunded':
                    Log::debug('Dispatching purchase.refunded handler', [
                        'purchase_id' => $purchaseData['id'] ?? null,
                    ]);
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
                'webhook_id' => $webhookId,
            ]);

            // Notify admin of webhook processing failure
            $eventType = $request->input('event', 'unknown');
            $purchaseId = $request->input('data.id');

            Notification::route('mail', config('mail.from.address'))
                ->notify(new WebhookProcessingFailed(
                    $eventType,
                    $e->getMessage(),
                    $purchaseId,
                    $request->all()
                ));

            return response('Internal Server Error', 500);
        }
    }
}
