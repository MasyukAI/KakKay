<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Notifications\WebhookProcessingFailed;
use App\Services\Chip\ChipDataRecorder;
use App\Services\Chip\WebhookProcessor;
use App\Support\ChipWebhookFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use MasyukAI\Chip\Services\WebhookService;
use Throwable;

final class ChipController extends Controller
{
    public function __construct(
        private readonly WebhookService $webhookService,
        private readonly WebhookProcessor $webhookProcessor,
        private readonly ChipDataRecorder $chipDataRecorder,
    ) {}

    /**
     * Central entry point for CHIP success callbacks and webhook deliveries.
     *
     * CHIP sends both success callbacks and webhooks to this endpoint with identical payloads
     * containing event_type fields. The only difference is:
     * - Success callback: POST to /webhooks/chip (no webhook_id in URL)
     * - Webhook: POST to /webhooks/chip/{webhook_id} (with webhook_id in URL)
     *
     * Both go through the WebhookProcessor for event-specific handling (e.g., purchase.paid).
     * Idempotency protection in CheckoutService ensures orders aren't duplicated when both arrive.
     */
    public function handle(Request $request, ?string $webhookId = null): Response
    {
        $payload = $request->all();
        $requestType = $webhookId !== null ? 'webhook' : 'success_callback';

        Log::debug('CHIP request received', [
            'type' => $requestType,
            'webhook_id' => $webhookId,
            'has_event_type' => isset($payload['event_type']),
            'event_type' => $payload['event_type'] ?? 'NOT_SET',
            'purchase_id' => $payload['id'] ?? 'NOT_SET',
            'reference' => $payload['reference'] ?? 'NOT_SET',
            'status' => $payload['status'] ?? 'NOT_SET',
            'request_method' => $request->method(),
            'request_url' => $request->fullUrl(),
            'user_agent' => $request->userAgent(),
        ]);

        $publicKey = $this->webhookService->getPublicKey($webhookId);

        if (! $this->webhookService->verifySignature($request, publicKey: $publicKey)) {
            Log::error('CHIP signature verification failed', [
                'type' => $requestType,
                'webhook_id' => $webhookId,
            ]);

            return response('Unauthorized', 401);
        }

        Log::debug('CHIP signature verified successfully', [
            'type' => $requestType,
            'webhook_id' => $webhookId,
        ]);

        $webhook = ChipWebhookFactory::fromRequest($request, $webhookId, $publicKey);

        $this->chipDataRecorder->recordWebhook($webhook);

        try {
            $this->webhookProcessor->handle($webhook);
            $this->chipDataRecorder->markWebhookProcessed($webhook->id, true);

            Log::info('CHIP request processed successfully', [
                'type' => $requestType,
                'webhook_id' => $webhookId,
                'event_type' => $webhook->event ?? $webhook->event_type,
                'purchase_id' => $webhook->data['id'] ?? null,
            ]);

            return response('OK', 200);
        } catch (Throwable $throwable) {
            $this->chipDataRecorder->markWebhookProcessed($webhook->id, false, $throwable->getMessage());

            Log::error('CHIP request processing failed', [
                'type' => $requestType,
                'webhook_id' => $webhookId,
                'event' => $webhook->event ?? $webhook->event_type,
                'purchase_id' => $webhook->data['id'] ?? null,
                'error' => $throwable->getMessage(),
                'error_class' => get_class($throwable),
            ]);

            Notification::route('mail', config('mail.from.address'))
                ->notify(new WebhookProcessingFailed(
                    $webhook->event ?? $webhook->event_type ?? 'unknown',
                    $throwable->getMessage(),
                    $webhook->data['id'] ?? null,
                    $request->all()
                ));

            return response('Internal Server Error', 500);
        }
    }
}
