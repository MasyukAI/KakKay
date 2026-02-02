<?php

declare(strict_types=1);

namespace App\Services\Chip;

use AIArmada\Chip\Data\WebhookData as Webhook;
use App\Notifications\OrderCreationFailed;
use App\Notifications\WebhookProcessingFailed;
use App\Services\CheckoutService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class WebhookProcessor
{
    public function __construct(
        private readonly CheckoutService $checkoutService,
        private readonly ChipDataRecorder $recorder,
    ) {}

    public function handle(Webhook $webhook): void
    {
        $event = $webhook->event ?? $webhook->event_type;
        $payload = $webhook->payload ?? [];
        $purchaseData = $webhook->data ?? Arr::get($payload, 'data', []);

        if (! is_string($event)) {
            Log::info('Received CHIP webhook without explicit event', [
                'webhook_id' => $webhook->id,
            ]);

            return;
        }

        Log::debug('Processing CHIP webhook event', [
            'webhook_id' => $webhook->id,
            'event' => $event,
            'purchase_id' => $purchaseData['id'] ?? Arr::get($purchaseData, 'purchase.id'),
        ]);

        if (is_array($purchaseData) && isset($purchaseData['id'])) {
            $this->recorder->upsertPurchase($purchaseData);
        }

        match ($event) {
            'purchase.paid' => $this->handlePurchasePaid($webhook, $purchaseData, $payload),
            'purchase.payment_failure' => $this->handlePaymentFailure($webhook, $purchaseData),
            default => $this->logInformationalEvent($webhook, $event, $purchaseData),
        };
    }

    private function handlePurchasePaid(Webhook $webhook, array $purchaseData, array $payload): void
    {
        $purchaseId = $purchaseData['id'] ?? null;

        if (! $purchaseId) {
            Log::warning('purchase.paid webhook missing purchase identifier', [
                'webhook_id' => $webhook->id,
            ]);

            return;
        }

        $mergedPayload = array_merge($payload, [
            'event' => 'purchase.paid',
            'purchase_id' => $purchaseId,
            'id' => $purchaseId,
            'source' => 'webhook',
            'webhook_id' => $webhook->id,
            'reference' => $purchaseData['reference']
                ?? Arr::get($purchaseData, 'purchase.reference')
                ?? Arr::get($payload, 'reference'),
            'amount' => $purchaseData['amount']
                ?? Arr::get($purchaseData, 'purchase.total')
                ?? Arr::get($payload, 'amount'),
        ]);

        try {
            $order = $this->checkoutService->handlePaymentSuccess($purchaseId, $mergedPayload);

            if (! $order) {
                Log::warning('purchase.paid webhook did not produce an order', [
                    'purchase_id' => $purchaseId,
                    'webhook_id' => $webhook->id,
                ]);
            }
        } catch (Throwable $throwable) {
            Log::error('Failed handling purchase.paid webhook', [
                'purchase_id' => $purchaseId,
                'error' => $throwable->getMessage(),
            ]);

            Notification::route('mail', config('mail.from.address'))
                ->notify(new WebhookProcessingFailed(
                    'purchase.paid',
                    $throwable->getMessage(),
                    $purchaseId,
                    $payload
                ));
        }
    }

    private function handlePaymentFailure(Webhook $webhook, array $purchaseData): void
    {
        $purchaseId = $purchaseData['id']
            ?? Arr::get($purchaseData, 'purchase.id');

        if (! $purchaseId) {
            Log::warning('purchase.payment_failure webhook missing purchase identifier', [
                'webhook_id' => $webhook->id,
            ]);

            return;
        }

        $failureReason = Arr::get($purchaseData, 'failure_reason')
            ?? Arr::get($purchaseData, 'purchase.failure_reason')
            ?? Arr::get($purchaseData, 'payment.failure_reason')
            ?? Arr::get($purchaseData, 'transaction_data.failure_reason')
            ?? Arr::get($purchaseData, 'error_message')
            ?? 'Payment failed at gateway';

        Log::warning('CHIP purchase payment failure reported', [
            'webhook_id' => $webhook->id,
            'purchase_id' => $purchaseId,
            'reason' => $failureReason,
        ]);

        // Update payment and order status in database
        try {
            $payment = \App\Models\Payment::where('gateway_payment_id', $purchaseId)->first();

            if ($payment) {
                $payment->update([
                    'status' => 'failed',
                    'gateway_transaction_id' => Arr::get($purchaseData, 'payment.id'),
                    'note' => $failureReason,
                    'failed_at' => now(),
                    'gateway_response' => $purchaseData,
                ]);

                // Update order status
                $order = $payment->order;
                if ($order && $order->status !== 'failed') {
                    $order->statusHistories()->create([
                        'from_status' => $order->status,
                        'to_status' => 'failed',
                        'actor_type' => 'gateway',
                        'note' => $failureReason,
                        'meta' => [
                            'gateway' => 'chip',
                            'purchase_id' => $purchaseId,
                            'failure_reason' => $failureReason,
                        ],
                    ]);

                    $order->update(['status' => 'failed']);
                }

                Log::info('Payment and order marked as failed', [
                    'payment_id' => $payment->id,
                    'order_id' => $order?->id,
                    'purchase_id' => $purchaseId,
                ]);
            } else {
                Log::warning('Payment not found for failed purchase', [
                    'purchase_id' => $purchaseId,
                ]);
            }
        } catch (Throwable $throwable) {
            Log::error('Failed to update payment/order status for payment failure', [
                'purchase_id' => $purchaseId,
                'error' => $throwable->getMessage(),
            ]);
        }

        Notification::route('mail', config('mail.from.address'))
            ->notify(new OrderCreationFailed(
                $purchaseId,
                'CHIP payment failure: '.$failureReason,
                [
                    'purchase_id' => $purchaseId,
                    'amount' => Arr::get($purchaseData, 'amount'),
                    'currency' => Arr::get($purchaseData, 'currency'),
                    'failure_reason' => $failureReason,
                ]
            ));
    }

    private function logInformationalEvent(Webhook $webhook, string $event, array $payload): void
    {
        Log::info('CHIP webhook received', [
            'event' => $event,
            'webhook_id' => $webhook->id,
            'purchase_id' => $payload['id'] ?? Arr::get($payload, 'purchase.id'),
        ]);
    }
}
