<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\ProcessWebhook;
use App\Services\Chip\ChipDataRecorder;
use App\Support\ChipWebhookFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use MasyukAI\Chip\Services\WebhookService;

final class ChipController extends Controller
{
    public function __construct(
        private readonly WebhookService $webhookService,
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

        // Dispatch webhook processing to queue for better reliability and performance
        ProcessWebhook::dispatch($webhook, $webhookId);

        Log::info('CHIP request queued for processing', [
            'type' => $requestType,
            'webhook_id' => $webhookId,
            'event_type' => $webhook->event ?? $webhook->event_type,
            'purchase_id' => $webhook->data['id'] ?? null,
        ]);

        return response('OK', 200);
    }
}
