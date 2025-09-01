<?php

declare(strict_types=1);

namespace Masyukai\Chip\Clients;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;
use Masyukai\Chip\Exceptions\ChipApiException;
use Masyukai\Chip\Exceptions\ChipValidationException;

class ChipSendClient
{
    public function __construct(
        protected string $apiKey,
        protected string $apiSecret,
        protected string $environment = 'sandbox',
        protected string $baseUrl = '',
        protected int $timeout = 30,
        protected array $retryConfig = []
    ) {
        //
    }

    protected function getBaseUrl(): string
    {
        if (!empty($this->baseUrl)) {
            return $this->baseUrl;
        }
        
        // Fallback to environment-based URL
        $baseUrls = [
            'sandbox' => 'https://api-sandbox.chip-in.asia/send',
            'production' => 'https://api.chip-in.asia/send',
        ];
        
        return $baseUrls[$this->environment] ?? $baseUrls['sandbox'];
    }

    /**
     * Make an authenticated HTTP request to CHIP Send API
     */
    public function request(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->getBaseUrl() . '/' . ltrim($endpoint, '/');
        $epoch = time();
        $checksum = $this->generateChecksum($epoch);

        if (config('chip.logging.log_requests')) {
            Log::channel(config('chip.logging.channel'))
                ->info('CHIP Send API Request', [
                    'method' => $method,
                    'url' => $url,
                    'data' => $data,
                ]);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'X-Timestamp' => (string) $epoch,
                'X-Signature' => $checksum,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => config('chip.defaults.creator_agent', 'MasyukAI/Chip Laravel Package'),
            ])
            ->timeout($this->timeout)
            ->send($method, $url, [
                'json' => $data
            ]);

            if (config('chip.logging.log_responses')) {
                Log::channel(config('chip.logging.channel'))
                    ->info('CHIP Send API Response', [
                        'status' => $response->status(),
                        'data' => $response->json(),
                    ]);
            }

            if ($response->failed()) {
                $this->handleFailedResponse($response);
            }

            return $response->json() ?? [];
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    protected function generateChecksum(int $epoch): string
    {
        return hash_hmac('sha256', (string) $epoch, $this->apiSecret);
    }

    protected function handleFailedResponse(Response $response): void
    {
        $statusCode = $response->status();
        $responseData = $response->json() ?? [];
        $message = $responseData['message'] ?? $responseData['error'] ?? "API request failed with status {$statusCode}";

        if ($statusCode === 400) {
            throw new ChipValidationException($message, $responseData, $statusCode);
        }

        throw new ChipApiException($message, $statusCode, $responseData);
    }

    protected function handleException(\Exception $e): void
    {
        Log::channel(config('chip.logging.channel'))
            ->error('CHIP Send API Request Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

        if ($e instanceof ChipApiException || $e instanceof ChipValidationException) {
            throw $e;
        }

        throw new ChipApiException('API request failed: ' . $e->getMessage(), 0, [], $e);
    }

    public function get(string $endpoint): array
    {
        return $this->request('GET', $endpoint);
    }

    public function post(string $endpoint, array $data = []): array
    {
        return $this->request('POST', $endpoint, $data);
    }

    public function put(string $endpoint, array $data = []): array
    {
        return $this->request('PUT', $endpoint, $data);
    }

    public function patch(string $endpoint, array $data = []): array
    {
        return $this->request('PATCH', $endpoint, $data);
    }

    public function delete(string $endpoint): array
    {
        return $this->request('DELETE', $endpoint);
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }
}
