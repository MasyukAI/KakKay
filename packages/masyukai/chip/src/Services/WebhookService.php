<?php

declare(strict_types=1);

namespace MasyukAI\Chip\Services;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use MasyukAI\Chip\Clients\ChipCollectClient;
use MasyukAI\Chip\Exceptions\WebhookVerificationException;

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
        } catch (Exception $e) {
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

        return Cache::remember(
            $cacheKey,
            config('chip.cache.ttl.public_key', 86400),
            function () use ($webhookId) {
                try {
                    if ($webhookId) {
                        $configuredKeys = (array) config('chip.webhooks.webhook_keys', []);

                        if (isset($configuredKeys[$webhookId]) && $configuredKeys[$webhookId] !== '') {
                            return (string) $configuredKeys[$webhookId];
                        }

                        $webhook = app(ChipCollectService::class)->getWebhook($webhookId);
                        $publicKey = (string) ($webhook['public_key'] ?? '');

                        if ($publicKey !== '') {
                            return $publicKey;
                        }
                    }

                    if (! $webhookId) {
                        $companyKey = config('chip.webhooks.company_public_key');
                        if ($companyKey) {
                            return (string) $companyKey;
                        }

                        $response = app(ChipCollectClient::class)->get('public_key/');

                        $publicKey = is_array($response)
                            ? (string) ($response['public_key'] ?? '')
                            : (string) $response;

                        if ($publicKey !== '') {
                            return $publicKey;
                        }
                    }
                } catch (Exception $e) {
                    Log::channel(config('chip.logging.channel'))
                        ->warning('Unable to resolve CHIP public key from API, using fallback', [
                            'webhook_id' => $webhookId,
                            'error' => $e->getMessage(),
                        ]);
                }

                $fallbackKeys = (array) config('chip.webhooks.webhook_keys', []);
                if ($webhookId && isset($fallbackKeys[$webhookId])) {
                    return (string) $fallbackKeys[$webhookId];
                }

                if (! $webhookId) {
                    $companyKey = config('chip.webhooks.company_public_key');
                    if ($companyKey) {
                        return (string) $companyKey;
                    }
                }

                return (string) config('chip.webhooks.public_key', '');
            }
        );
    }

    public function parsePayload(string $payload): object
    {
        $data = json_decode($payload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new WebhookVerificationException('Invalid JSON payload');
        }

        return (object) $data;
    }
}
