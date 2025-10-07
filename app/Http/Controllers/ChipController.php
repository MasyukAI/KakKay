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
     * CHIP sends the same payload shape for both redirect callbacks and
     * webhook events, the only distinction being the presence of a
     * configured webhook identifier. We verify the signature, normalise
     * the payload into the package's Webhook data object and pass it to
     * the domain processor for further action.
     */
    public function handle(Request $request, ?string $webhookId = null): Response
    {
        $publicKey = $this->webhookService->getPublicKey($webhookId);

        if (! $this->webhookService->verifySignature($request, publicKey: $publicKey)) {
            Log::error('CHIP webhook signature verification failed', [
                'webhook_id' => $webhookId,
            ]);

            return response('Unauthorized', 401);
        }

        $webhook = ChipWebhookFactory::fromRequest($request, $webhookId, $publicKey);

        $this->chipDataRecorder->recordWebhook($webhook);

        try {
            $this->webhookProcessor->handle($webhook);
            $this->chipDataRecorder->markWebhookProcessed($webhook->id, true);

            return response('OK', 200);
        } catch (Throwable $throwable) {
            $this->chipDataRecorder->markWebhookProcessed($webhook->id, false, $throwable->getMessage());

            Log::error('CHIP webhook processing failed', [
                'webhook_id' => $webhookId,
                'event' => $webhook->event ?? $webhook->event_type,
                'error' => $throwable->getMessage(),
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
