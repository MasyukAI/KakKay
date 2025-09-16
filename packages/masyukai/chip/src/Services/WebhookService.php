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
    public function verifySignature($payloadOrRequest, ?string $signature = null, ?string $publicKey = null): bool
    {
        // Handle both method signatures for backward compatibility
        if ($payloadOrRequest instanceof \Illuminate\Http\Request) {
            $request = $payloadOrRequest;
            $payload = $request->getContent();
            $signature = $signature ?? $request->header('X-Signature');
            $publicKey = $publicKey ?? $this->getPublicKey();
        } else {
            $payload = $payloadOrRequest;
        }

        if (! config('chip.webhooks.verify_signature')) {
            return true;
        }

        if (! $signature) {
            throw new WebhookVerificationException('Missing signature header');
        }

        if (! $publicKey) {
            throw new WebhookVerificationException('No public key configured');
        }

        try {
            // Remove the "-----BEGIN PUBLIC KEY-----" and "-----END PUBLIC KEY-----" wrapper
            $cleanedKey = preg_replace('/-----[^-]+-----/', '', $publicKey);
            $cleanedKey = str_replace(["\n", "\r", ' '], '', $cleanedKey);

            // Create a proper PEM format
            $pemKey = "-----BEGIN PUBLIC KEY-----\n".chunk_split($cleanedKey, 64, "\n").'-----END PUBLIC KEY-----';

            // Create the public key resource
            $publicKeyResource = openssl_pkey_get_public($pemKey);
            if (! $publicKeyResource) {
                throw new WebhookVerificationException('Invalid public key format');
            }

            // Hash the payload
            $hash = hash('sha256', $payload, true);

            // Verify the signature
            $verified = openssl_verify($hash, base64_decode($signature), $publicKeyResource, OPENSSL_ALGO_SHA256);

            return $verified === 1;
        } catch (\Exception $e) {
            Log::channel(config('chip.logging.channel'))
                ->error('Webhook signature verification failed', [
                    'error' => $e->getMessage(),
                    'signature' => $signature,
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

                    return $webhook['public_key'] ?? $webhook->public_key ?? '';
                }

                // Get general public key for success callbacks
                $response = app(ChipCollectService::class)->getPublicKey();

                return $response['public_key'] ?? '';
            } catch (\Exception $e) {
                // Fallback to configured public key if API call fails
                return config('chip.webhooks.public_key', '');
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
                'data' => $data,
            ]);

        // Dispatch generic webhook event
        $webhook = \MasyukAI\Chip\DataObjects\Webhook::fromArray([
            'event' => $event,
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ]);
        Event::dispatch(new WebhookReceived($webhook));

        // Handle specific events
        $this->handleSpecificEvent($event, $data);

        return true;
    }

    /**
     * Handle specific webhook events.
     */
    protected function handleSpecificEvent(string $event, array $data): void
    {
        $eventMapping = config('chip.webhooks.event_mapping', []);

        if (! isset($eventMapping[$event])) {
            return;
        }

        switch ($event) {
            case 'purchase.created':
                if (class_exists(PurchaseCreated::class)) {
                    $purchase = Purchase::fromArray($data);
                    Event::dispatch(new PurchaseCreated($purchase));
                }
                break;

            case 'purchase.paid':
                if (class_exists(PurchasePaid::class)) {
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
        $allowedEvents = config('chip.webhooks.allowed_events', []);

        return in_array($event, $allowedEvents) || in_array('*', $allowedEvents);
    }

    /**
     * Get webhook configuration.
     */
    public function getWebhookConfig(): array
    {
        return config('chip.webhooks', []);
    }
}
