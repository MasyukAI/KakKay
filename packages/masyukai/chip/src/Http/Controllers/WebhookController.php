<?php

declare(strict_types=1);

namespace Masyukai\Chip\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
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
            // Use the webhook service to process the webhook
            $this->webhookService->processWebhook($request);

            return response()->json(['status' => 'success'], 200);
        } catch (\Exception $e) {
            Log::channel(config('chip.logging.channel'))
                ->error('CHIP Webhook processing failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

            return response()->json(['status' => 'error', 'message' => 'Error processing webhook'], 400);
        }
    }

    public function handleSuccess(Request $request): Response
    {
        try {
            $signature = $request->header('X-Signature');
            $payload = $request->getContent();

            if (!$signature || !$payload) {
                return response()->json(['status' => 'error', 'message' => 'Missing signature or payload'], 400);
            }

            // Verify signature using general public key
            $publicKey = $this->webhookService->getPublicKey();
            
            if (!$this->webhookService->verifySignature($payload, $signature, $publicKey)) {
                return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 401);
            }

            $parsedPayload = $this->webhookService->parsePayload($payload);

            Log::channel(config('chip.logging.channel'))
                ->info('CHIP Success callback received', [
                    'payload' => $parsedPayload,
                ]);

            // Dispatch the webhook event
            if (config('chip.events.dispatch_webhook_events')) {
                event(new WebhookReceived(
                    eventType: 'purchase.success',
                    payload: $parsedPayload,
                    headers: $request->headers->all()
                ));
            }

            return response()->json(['status' => 'success'], 200);
        } catch (\Exception $e) {
            Log::channel(config('chip.logging.channel'))
                ->error('CHIP Success callback processing failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

            return response()->json(['status' => 'error', 'message' => 'Error processing callback'], 400);
        }
    }
}
