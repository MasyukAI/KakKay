<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use MasyukAI\Chip\Commands\ChipHealthCheckCommand;

describe('ChipHealthCheckCommand', function () {
    beforeEach(function () {
        config([
            'chip.environment' => 'sandbox',
            'chip.collect.brand_id' => 'test-brand-id',
            'chip.collect.secret_key' => 'test-secret-key',
            'chip.send.brand_id' => 'test-send-brand-id',
            'chip.send.api_key' => 'test-send-api-key',
            'chip.send.api_secret' => 'test-send-api-secret',
            'chip.logging.enabled' => true,
            'chip.webhooks.events' => ['purchase.paid', 'purchase.refunded'],
        ]);
    });

    it('checks both CHIP Collect and Send APIs by default', function () {
        Http::fake([
            '*' => Http::response(['payment_methods' => [], 'bank_accounts' => []], 200),
        ]);

        $this->artisan(ChipHealthCheckCommand::class)
            ->expectsOutput('🔍 CHIP API Health Check')
            ->expectsOutputToContain('Checking CHIP Collect API')
            ->expectsOutputToContain('Checking CHIP Send API')
            ->assertExitCode(0);
    });

    it('checks only CHIP Collect API with --collect flag', function () {
        Http::fake([
            '*' => Http::response(['payment_methods' => []], 200),
        ]);

        $this->artisan(ChipHealthCheckCommand::class, ['--collect' => true])
            ->expectsOutputToContain('Checking CHIP Collect API')
            ->doesntExpectOutputToContain('Checking CHIP Send API')
            ->assertExitCode(0);
    });

    it('checks only CHIP Send API with --send flag', function () {
        Http::fake([
            '*' => Http::response(['bank_accounts' => []], 200),
        ]);

        $this->artisan(ChipHealthCheckCommand::class, ['--send' => true])
            ->doesntExpectOutputToContain('Checking CHIP Collect API')
            ->expectsOutputToContain('Checking CHIP Send API')
            ->assertExitCode(0);
    });

    it('returns exit code 1 when CHIP Collect API is unreachable', function () {
        Http::fake([
            '*/payment_methods/*' => Http::response(['error' => 'Unauthorized'], 401),
            '*/payment_methods' => Http::response(['error' => 'Unauthorized'], 401),
            '*/bank_accounts' => Http::response(['bank_accounts' => []], 200),
        ]);

        $this->artisan(ChipHealthCheckCommand::class)
            ->expectsOutputToContain('❌')
            ->assertExitCode(1);
    });

    it('returns exit code 1 when CHIP Send API is unreachable', function () {
        Http::fake([
            '*/payment_methods/*' => Http::response(['payment_methods' => []], 200),
            '*/payment_methods' => Http::response(['payment_methods' => []], 200),
            '*/bank_accounts' => Http::response(['error' => 'Unauthorized'], 401),
        ]);

        $this->artisan(ChipHealthCheckCommand::class)
            ->expectsOutputToContain('❌')
            ->assertExitCode(1);
    });

    it('shows all systems operational when all checks pass', function () {
        Http::fake([
            '*' => Http::response(['payment_methods' => [], 'bank_accounts' => []], 200),
        ]);

        $this->artisan(ChipHealthCheckCommand::class)
            ->expectsOutput('✅ All systems operational')
            ->assertExitCode(0);
    });
});
