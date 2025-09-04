<?php

declare(strict_types=1);

namespace Masyukai\Chip\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Masyukai\Chip\DataObjects\Webhook;
use Masyukai\Chip\Events\WebhookReceived;
use Masyukai\Chip\Http\Requests\WebhookRequest;
use Masyukai\Chip\Services\WebhookService;

class WebhookController extends Controller
{
    public function __construct(
        protected WebhookService $webhookService
    ) {}

    public function handle(WebhookRequest $request): Response
    {
        try {
            $payload = $request->getWebhookPayload();
            $headers = $request->getHeaders();

            Log::channel(config('chip.logging.channel'))
                ->info('CHIP Webhook received', [
                    'event_type' => $payload->event_type ?? 'unknown',
                    'payload' => $payload,
                ]);

            // Dispatch the webhook event
            if (config('chip.events.dispatch_webhook_events')) {
                $webhook = Webhook::fromArray([
                    'event' => $payload->event ?? 'unknown',
                    'data' => (array) $payload,
                    'timestamp' => now()->toISOString(),
                ]);

                event(new WebhookReceived($webhook));
            }

            return response('OK', 200);
        } catch (\Exception $e) {
            Log::channel(config('chip.logging.channel'))
                ->error('CHIP Webhook processing failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

            return response('Error processing webhook', 400);
        }
    }

    public function handleSuccess(Request $request): Response
    {
        try {
            $signature = $request->header('X-Signature');
            $payload = $request->getContent();

            if (! $signature || ! $payload) {
                return response('Missing signature or payload', 400);
            }

            // Verify signature using general public key
            $publicKey = $this->webhookService->getPublicKey();

            if (! $this->webhookService->verifySignature($payload, $signature, $publicKey)) {
                return response('Invalid signature', 401);
            }

            $parsedPayload = $this->webhookService->parsePayload($payload);

            Log::channel(config('chip.logging.channel'))
                ->info('CHIP Success callback received', [
                    'payload' => $parsedPayload,
                ]);

            // Dispatch the webhook event
            if (config('chip.events.dispatch_webhook_events')) {
                $webhook = Webhook::fromArray([
                    'event' => 'purchase.success',
                    'data' => (array) $parsedPayload,
                    'timestamp' => now()->toISOString(),
                ]);

                event(new WebhookReceived($webhook));
            }

            return response('OK', 200);
        } catch (\Exception $e) {
            Log::channel(config('chip.logging.channel'))
                ->error('CHIP Success callback processing failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

            return response('Error processing callback', 400);
        }
    }
}
