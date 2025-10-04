<?php

declare(strict_types=1);

namespace MasyukAI\Chip\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use MasyukAI\Chip\DataObjects\Purchase;
use MasyukAI\Chip\Events\PurchaseCreated;
use MasyukAI\Chip\Events\PurchasePaid;
use MasyukAI\Chip\Events\WebhookReceived;
use MasyukAI\Chip\Exceptions\WebhookVerificationException;
use MasyukAI\Chip\Http\Requests\WebhookRequest;

class WebhookService
{
    /**
     * Verify webhook signature using Request object
     */
    public function verifySignature(Request|string $payloadOrRequest, ?string $signature = null, ?string $publicKey = null): bool
    {
        if ($payloadOrRequest instanceof Request) {
            $payload = $payloadOrRequest->getContent();
            $signature ??= $payloadOrRequest->header('X-Signature');
        } else {
            $payload = (string) $payloadOrRequest;
        }

        if (! config('chip.webhooks.verify_signature')) {
            return true;
        }

        $publicKey = $publicKey ?? $this->getPublicKey();

        if (! $signature) {
            throw new WebhookVerificationException('Missing signature header');
        }

        if (! $publicKey) {
            throw new WebhookVerificationException('No public key configured');
        }

        try {
            $pemKey = str_contains($publicKey, 'BEGIN PUBLIC KEY')
                ? $publicKey
                : "-----BEGIN PUBLIC KEY-----\n".chunk_split(str_replace(["\n", "\r", ' '], '', $publicKey), 64, "\n").'-----END PUBLIC KEY-----';

            $publicKeyResource = openssl_pkey_get_public($pemKey);
            if (! $publicKeyResource) {
                throw new WebhookVerificationException('Invalid public key format');
            }

            $decodedSignature = base64_decode($signature, true);
            if ($decodedSignature === false) {
                throw new WebhookVerificationException('Signature is not valid base64');
            }

            $verified = openssl_verify($payload, $decodedSignature, $publicKeyResource, OPENSSL_ALGO_SHA256);

            return $verified === 1;
        } catch (\Exception $e) {
            Log::channel(config('chip.logging.channel'))
                ->error('Webhook signature verification failed', [
                    'error' => $e->getMessage(),
                ]);

            throw new WebhookVerificationException('Signature verification failed: '.$e->getMessage());
        }
    }

    public function getPublicKey(?string $webhookId = null): string
    {
        $cacheKey = config('chip.cache.prefix').'public_key'.($webhookId ? ":{$webhookId}" : '');
        $ttl = config('chip.cache.ttl.public_key');

        return Cache::remember($cacheKey, $ttl, function () use ($webhookId) {
            try {
                if ($webhookId) {
                    // Get webhook-specific public key
                    $webhook = app(ChipCollectService::class)->getWebhook($webhookId);

                    return (string) ($webhook['public_key'] ?? '');
                }

                // Get general public key for success callbacks
                $response = app(ChipCollectService::class)->getPublicKey();

                if (is_array($response)) {
                    return (string) ($response['public_key'] ?? '');
                }

                return (string) $response;
            } catch (\Exception $e) {
                // Fallback to configured public key if API call fails
                return (string) config('chip.webhooks.public_key', '');
            }
        });
    }

    public function parsePayload(string $payload): object
    {
        $data = json_decode($payload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new WebhookVerificationException('Invalid JSON payload');
        }

        return (object) $data;
    }

    /**
     * @param array<string, mixed> $eventConfig
     */
    public function shouldProcessWebhook(string $eventType, array $eventConfig = []): bool
    {
        // You can add custom logic here to determine if a webhook should be processed
        // based on event type, configuration, etc.

        return true;
    }

    /**
     * Process incoming webhook request.
     *
     * @throws WebhookVerificationException
     */
    public function processWebhook(WebhookRequest $request): bool
    {
        if (! $this->verifySignature($request)) {
            throw new WebhookVerificationException('Invalid webhook signature');
        }

        $event = $request->getEvent();
        $data = $request->getData();

        // Log webhook receipt
        Log::channel(config('chip.logging.channel'))
            ->info('Webhook received', [
                'event' => $event,
                'reference' => $data['id'] ?? null,
            ]);

        // Dispatch generic webhook event
        $webhook = \MasyukAI\Chip\DataObjects\Webhook::fromArray([
            'event' => $event,
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ]);
        Event::dispatch(new WebhookReceived($webhook));

        // Handle specific events
        if ($event !== null) {
            $this->handleSpecificEvent($event, $data);
        }

        return true;
    }

    /**
     * Handle specific webhook events.
     *
     * @param array<string, mixed> $data
     */
    protected function handleSpecificEvent(string $event, array $data): void
    {
        $eventMapping = config('chip.webhooks.event_mapping', []);

        if (! isset($eventMapping[$event])) {
            return;
        }

        switch ($event) {
            case 'purchase.created':
                if (config('chip.events.dispatch_purchase_events', true)
                    && class_exists(PurchaseCreated::class)) {
                    $purchase = Purchase::fromArray($data);
                    Event::dispatch(new PurchaseCreated($purchase));
                }
                break;

            case 'purchase.paid':
                if (config('chip.events.dispatch_purchase_events', true)
                    && class_exists(PurchasePaid::class)) {
                    $purchase = Purchase::fromArray($data);
                    Event::dispatch(new PurchasePaid($purchase));
                }
                break;
        }
    }

    /**
     * Check if webhook event is allowed.
     */
    public function isEventAllowed(string $event): bool
    {
        $allowedEvents = (array) config('chip.webhooks.allowed_events', []);

        return in_array($event, $allowedEvents) || in_array('*', $allowedEvents);
    }

    /**
     * Get webhook configuration.
     *
     * @return array<string, mixed>
     */
    public function getWebhookConfig(): array
    {
        $config = config('chip.webhooks', []);
        return is_array($config) ? $config : [];
    }
}
