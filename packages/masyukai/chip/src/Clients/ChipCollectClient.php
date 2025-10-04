<?php

declare(strict_types=1);

namespace MasyukAI\Chip\Clients;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use MasyukAI\Chip\Exceptions\ChipApiException;

class ChipCollectClient
{
    public function __construct(
        protected string $apiKey,
        protected string $brandId,
        protected string $baseUrl = 'https://gate.chip-in.asia/api/v1/',
        protected int $timeout = 30,
        protected array $retryConfig = []
    ) {
        //
    }

    protected function getBaseUrl(): string
    {
        return rtrim($this->baseUrl, '/');
    }

    /**
     * Make an HTTP request using Laravel's HTTP client with retry logic
     */
    public function request(string $method, string $endpoint, array $data = [], array $headers = []): array
    {
        $url = $this->getBaseUrl().'/'.ltrim($endpoint, '/');

        // Log request if enabled
        if (config('chip.logging.enabled') && config('chip.logging.log_requests')) {
            Log::channel(config('chip.logging.channel', 'stack'))
                ->info('CHIP Collect API Request', [
                    'method' => $method,
                    'url' => $url,
                    'data' => config('chip.logging.mask_sensitive_data', true) ? $this->maskSensitiveData($data) : $data,
                ]);
        }

        $attempts = max(1, (int) ($this->retryConfig['attempts'] ?? 1));
        $delayMilliseconds = max(0, (int) ($this->retryConfig['delay'] ?? 0));

        $response = null;

        try {
            for ($attempt = 1; $attempt <= $attempts; $attempt++) {
                try {
                    $response = $this->makeRequest($method, $url, $data, $headers);

                    if ($response->failed() && $attempt < $attempts && $this->shouldRetry(null, $response)) {
                        usleep($delayMilliseconds * 1000);

                        continue;
                    }

                    if ($response->failed()) {
                        $this->handleFailedResponse($response);
                    }

                    break;
                } catch (\Exception $e) {
                    if ($attempt >= $attempts || ! $this->shouldRetry($e, null)) {
                        throw $e;
                    }

                    usleep($delayMilliseconds * 1000);
                }
            }

            // Log response if enabled
            if ($response instanceof Response && config('chip.logging.enabled') && config('chip.logging.log_responses')) {
                Log::channel(config('chip.logging.channel', 'stack'))
                    ->info('CHIP Collect API Response', [
                        'status' => $response->status(),
                        'data' => config('chip.logging.mask_sensitive_data', true) ? $this->maskSensitiveData($response->json() ?? []) : $response->json(),
                    ]);
            }

            return $response?->json() ?? [];
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Make the actual HTTP request
     */
    protected function makeRequest(string $method, string $url, array $data, array $headers = []): Response
    {
        $defaultHeaders = [
            'Authorization' => "Bearer {$this->apiKey}",
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Agent' => config('chip.defaults.creator_agent', 'MasyukAI/Chip Laravel Package'),
        ];

        return Http::withHeaders(array_merge($defaultHeaders, $headers))
            ->timeout($this->timeout)
            ->send($method, $url, [
                'json' => $data,
            ]);
    }

    /**
     * Determine if the request should be retried
     */
    protected function shouldRetry(?\Throwable $exception, ?Response $response): bool
    {
        if ($exception !== null) {
            if ($exception instanceof ChipApiException) {
                return $exception->getStatusCode() >= 500;
            }

            return $exception instanceof ConnectionException;
        }

        // Retry on 5xx server errors
        if ($response !== null) {
            return $response->serverError();
        }

        return false;
    }

    /**
     * Mask sensitive data in logs
     */
    protected function maskSensitiveData(array $data): array
    {
        $masked = $data;

        // Mask common sensitive fields
        $sensitiveFields = ['api_key', 'secret', 'password', 'token', 'card_number', 'cvv'];

        foreach ($sensitiveFields as $field) {
            if (isset($masked[$field])) {
                $masked[$field] = '***MASKED***';
            }
        }

        return $masked;
    }

    protected function handleFailedResponse(Response $response): never
    {
        $statusCode = $response->status();
        $responseData = $response->json() ?? [];
        $message = $responseData['message'] ?? $responseData['error'] ?? "API request failed with status {$statusCode}";

        throw new ChipApiException($message, $statusCode, $responseData);
    }

    protected function handleException(\Exception $e): never
    {
        if (config('chip.logging.enabled')) {
            Log::channel(config('chip.logging.channel', 'stack'))
                ->error('CHIP Collect API Request Failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
        }

        if ($e instanceof ChipApiException) {
            throw $e;
        }

        throw new ChipApiException('API request failed: '.$e->getMessage(), 0, [], $e);
    }

    /**
     * Get data from the API. Returns array for most endpoints, string for public_key/ endpoint.
     */
    public function get(string $endpoint): array|string
    {
        if ($endpoint === 'public_key/' || $endpoint === '/public_key/') {
            $url = $this->getBaseUrl().'/'.ltrim($endpoint, '/');
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => config('chip.defaults.creator_agent', 'MasyukAI/Chip Laravel Package'),
            ])->timeout($this->timeout)->get($url);

            if ($response->failed()) {
                $this->handleFailedResponse($response);
            }

            return $response->body(); // PEM string
        }

        return $this->request('GET', $endpoint);
    }

    public function post(string $endpoint, array $data = [], array $headers = []): array
    {
        return $this->request('POST', $endpoint, $data, $headers);
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

    public function getBrandId(): string
    {
        return $this->brandId;
    }
}
