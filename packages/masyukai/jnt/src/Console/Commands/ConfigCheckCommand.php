<?php

declare(strict_types=1);

namespace MasyukAI\Jnt\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use MasyukAI\Jnt\Services\JntExpressService;

class ConfigCheckCommand extends Command
{
    protected $signature = 'jnt:config:check';

    protected $description = 'Validate J&T Express configuration and connectivity';

    public function handle(JntExpressService $jnt): int
    {
        $this->info('J&T Express Configuration Check');
        $this->newLine();

        $checks = [];
        $hasErrors = false;

        // Check API Account
        $checks[] = $this->checkConfig('API Account', 'jnt.api_account');

        // Check Private Key
        $checks[] = $this->checkPrivateKey();

        // Check Public Key
        $checks[] = $this->checkPublicKey();

        // Check Environment
        $checks[] = $this->checkEnvironment();

        // Check Base URL
        $checks[] = $this->checkBaseUrl();

        // Display results table
        $this->table(
            ['Configuration', 'Status', 'Details'],
            collect($checks)->map(fn ($check) => [
                $check['name'],
                $check['valid'] ? '✓' : '✗',
                $check['message'],
            ])->toArray()
        );

        $this->newLine();

        // Check for any errors
        $hasErrors = collect($checks)->contains('valid', false);

        if ($hasErrors) {
            $this->error('Configuration validation failed. Please fix the errors above.');

            return self::FAILURE;
        }

        // Test API connectivity
        $this->info('Testing API connectivity...');
        $connectivityCheck = $this->testConnectivity($jnt);

        if (! $connectivityCheck['success']) {
            $this->error('Connectivity test failed: '.$connectivityCheck['message']);

            return self::FAILURE;
        }

        $this->info('✓ All checks passed! J&T Express is properly configured.');

        return self::SUCCESS;
    }

    private function checkConfig(string $name, string $key): array
    {
        $value = config($key);

        if (empty($value)) {
            return [
                'name' => $name,
                'valid' => false,
                'message' => "Missing - Set {$key} in config or environment",
            ];
        }

        return [
            'name' => $name,
            'valid' => true,
            'message' => 'Configured',
        ];
    }

    private function checkPrivateKey(): array
    {
        $privateKey = config('jnt.private_key');

        if (empty($privateKey)) {
            return [
                'name' => 'Private Key',
                'valid' => false,
                'message' => 'Missing - Required for signing requests',
            ];
        }

        // Validate RSA private key format
        if (! str_contains($privateKey, 'BEGIN RSA PRIVATE KEY') && ! str_contains($privateKey, 'BEGIN PRIVATE KEY')) {
            return [
                'name' => 'Private Key',
                'valid' => false,
                'message' => 'Invalid format - Must be valid RSA private key',
            ];
        }

        return [
            'name' => 'Private Key',
            'valid' => true,
            'message' => 'Valid RSA private key',
        ];
    }

    private function checkPublicKey(): array
    {
        $publicKey = config('jnt.public_key');

        if (empty($publicKey)) {
            return [
                'name' => 'Public Key',
                'valid' => false,
                'message' => 'Missing - Required for webhook verification',
            ];
        }

        // Validate RSA public key format
        if (! str_contains($publicKey, 'BEGIN PUBLIC KEY')) {
            return [
                'name' => 'Public Key',
                'valid' => false,
                'message' => 'Invalid format - Must be valid RSA public key',
            ];
        }

        return [
            'name' => 'Public Key',
            'valid' => true,
            'message' => 'Valid RSA public key',
        ];
    }

    private function checkEnvironment(): array
    {
        $environment = config('jnt.environment', 'production');

        if (! in_array($environment, ['production', 'sandbox'])) {
            return [
                'name' => 'Environment',
                'valid' => false,
                'message' => "Invalid - Must be 'production' or 'sandbox'",
            ];
        }

        return [
            'name' => 'Environment',
            'valid' => true,
            'message' => ucfirst($environment),
        ];
    }

    private function checkBaseUrl(): array
    {
        $baseUrl = config('jnt.base_url');

        if (empty($baseUrl)) {
            return [
                'name' => 'Base URL',
                'valid' => false,
                'message' => 'Missing - Required for API calls',
            ];
        }

        if (! filter_var($baseUrl, FILTER_VALIDATE_URL)) {
            return [
                'name' => 'Base URL',
                'valid' => false,
                'message' => 'Invalid URL format',
            ];
        }

        return [
            'name' => 'Base URL',
            'valid' => true,
            'message' => $baseUrl,
        ];
    }

    private function testConnectivity(JntExpressService $jnt): array
    {
        try {
            $baseUrl = config('jnt.base_url');

            // Simple connectivity test - check if we can reach the API
            $response = Http::timeout(5)->get($baseUrl);

            // Check if response is successful (2xx status code)
            if (! $response->successful()) {
                return [
                    'success' => false,
                    'message' => "HTTP {$response->status()} error from API endpoint",
                ];
            }

            return [
                'success' => true,
                'message' => 'API endpoint is reachable',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
