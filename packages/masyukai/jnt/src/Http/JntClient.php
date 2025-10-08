<?php

declare(strict_types=1);

namespace MasyukAI\Jnt\Http;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use MasyukAI\Jnt\Exceptions\JntException;

class JntClient
{
    public function __construct(
        protected readonly string $baseUrl,
        protected readonly string $apiAccount,
        protected readonly string $privateKey,
        protected readonly array $config = [],
    ) {}

    public function post(string $endpoint, array $bizContent): array
    {
        $jsonBizContent = json_encode($bizContent, JSON_UNESCAPED_UNICODE);

        if ($jsonBizContent === false) {
            throw JntException::apiError('Failed to encode bizContent to JSON');
        }

        $digest = $this->generateDigest($jsonBizContent);
        $timestamp = (int) (microtime(true) * 1000);

        $this->logRequest($endpoint, $bizContent);

        $retryTimes = $this->config['http']['retry_times'] ?? 3;
        $retrySleep = $this->config['http']['retry_sleep'] ?? 1000;

        try {
            $response = Http::timeout($this->config['http']['timeout'] ?? 30)
                ->connectTimeout($this->config['http']['connect_timeout'] ?? 10)
                ->retry($retryTimes, $retrySleep, function ($exception, $request) {
                    // Retry on connection exceptions
                    if ($exception instanceof ConnectionException) {
                        return true;
                    }

                    // Don't retry for other exceptions
                    return false;
                }, throw: false)
                ->withHeaders([
                    'apiAccount' => $this->apiAccount,
                    'digest' => $digest,
                    'timestamp' => (string) $timestamp,
                ])
                ->asForm()
                ->post("{$this->baseUrl}{$endpoint}", [
                    'bizContent' => $jsonBizContent,
                ]);

            $this->logResponse($response);

            // If we got a 5xx error, retry manually
            if ($response->status() >= 500 && $response->status() < 600) {
                for ($attempt = 2; $attempt <= $retryTimes; $attempt++) {
                    usleep($retrySleep * 1000);

                    $response = Http::timeout($this->config['http']['timeout'] ?? 30)
                        ->connectTimeout($this->config['http']['connect_timeout'] ?? 10)
                        ->withHeaders([
                            'apiAccount' => $this->apiAccount,
                            'digest' => $digest,
                            'timestamp' => (string) $timestamp,
                        ])
                        ->asForm()
                        ->post("{$this->baseUrl}{$endpoint}", [
                            'bizContent' => $jsonBizContent,
                        ]);

                    $this->logResponse($response);

                    if ($response->status() < 500) {
                        break;
                    }
                }
            }

            if ($response->failed()) {
                throw JntException::apiError(
                    "HTTP {$response->status()}: {$response->body()}",
                    (string) $response->status()
                );
            }

            $data = $response->json();

            if ($data === null) {
                throw JntException::apiError('Failed to decode API response: invalid JSON');
            }

            // Check for API-level errors
            if (isset($data['code']) && (string) $data['code'] !== '1') {
                throw JntException::apiError(
                    $data['msg'] ?? 'API request failed',
                    (string) $data['code'],
                    $data['data'] ?? null
                );
            }

            return $data;
        } catch (ConnectionException $e) {
            throw JntException::apiError(
                'Connection failed: '.$e->getMessage(),
                '0'
            );
        }
    }

    public function verifyWebhookSignature(string $bizContent, string $digest): bool
    {
        $expectedDigest = $this->generateDigest($bizContent);

        return hash_equals($expectedDigest, $digest);
    }

    protected function shouldRetry(mixed $exception): bool
    {
        // Retry on connection errors
        if ($exception instanceof ConnectionException) {
            return true;
        }

        return false;
    }

    protected function generateDigest(string $bizContent): string
    {
        $toSign = $bizContent.$this->privateKey;
        $md5Raw = md5($toSign, true);

        return base64_encode($md5Raw);
    }

    protected function logRequest(string $endpoint, array $data): void
    {
        if (! ($this->config['logging']['enabled'] ?? true)) {
            return;
        }

        $channel = $this->config['logging']['channel'] ?? 'stack';
        $level = $this->config['logging']['level'] ?? 'info';

        Log::channel($channel)->log($level, 'J&T API Request', [
            'endpoint' => $endpoint,
            'data' => $this->maskSensitiveData($data),
        ]);
    }

    protected function logResponse(Response $response): void
    {
        if (! ($this->config['logging']['enabled'] ?? true)) {
            return;
        }

        $channel = $this->config['logging']['channel'] ?? 'stack';
        $level = $this->config['logging']['level'] ?? 'info';

        Log::channel($channel)->log($level, 'J&T API Response', [
            'status' => $response->status(),
            'successful' => $response->successful(),
            'body' => mb_substr($response->body(), 0, 500),
        ]);
    }

    protected function maskSensitiveData(array $data): array
    {
        $masked = $data;

        if (isset($masked['password'])) {
            $masked['password'] = '***MASKED***';
        }

        if (isset($masked['customerCode'])) {
            $masked['customerCode'] = mb_substr($masked['customerCode'], 0, 3).'***';
        }

        return $masked;
    }
}
