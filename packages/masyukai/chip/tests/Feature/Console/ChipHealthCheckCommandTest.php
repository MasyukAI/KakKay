<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use MasyukAI\Chip\Commands\ChipHealthCheckCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

describe('ChipHealthCheckCommand', function (): void {
    beforeEach(function (): void {
        config([
            'chip.environment' => 'sandbox',
            'chip.collect.brand_id' => 'test-brand-id',
            'chip.collect.api_key' => 'test-collect-api-key',
            'chip.collect.secret_key' => 'test-secret-key',
            'chip.send.brand_id' => 'test-send-brand-id',
            'chip.send.api_key' => 'test-send-api-key',
            'chip.send.api_secret' => 'test-send-api-secret',
            'chip.logging.enabled' => true,
            'chip.logging.log_requests' => true,
            'chip.webhooks.events' => ['purchase.paid', 'purchase.refunded'],
            'chip.events.dispatch_webhook_events' => true,
            'chip.send.environment' => 'sandbox',
        ]);
    });

    it('checks both CHIP Collect and Send APIs by default', function (): void {
        Http::fake([
            '*/payment_methods*' => Http::response([], 200),
            '*/accounts' => Http::response([], 200),
        ]);

        $this->artisan(ChipHealthCheckCommand::class)
            ->expectsOutput('🔍 CHIP API Health Check')
            ->expectsOutputToContain('Checking CHIP Collect API')
            ->expectsOutputToContain('Checking CHIP Send API')
            ->assertExitCode(0);
    });

    it('displays verbose diagnostics when requested', function (): void {
        Http::fake([
            '*/payment_methods*' => Http::response(['fpx'], 200),
            '*/accounts' => Http::response(['acc_1'], 200),
        ]);

        $command = app(ChipHealthCheckCommand::class);
        $command->setLaravel(app());

        $definition = $command->getDefinition();
        $input = new class([], $definition) extends ArrayInput {
            public function getOption(string $name): mixed
            {
                if ($name === 'verbose') {
                    return true;
                }

                return parent::getOption($name);
            }
        };
    $output = new BufferedOutput(OutputInterface::VERBOSITY_VERBOSE);

    $status = $command->run($input, $output);

        $buffer = $output->fetch();

    expect($status)->toBe(0);
        expect($buffer)->toContain('Environment:');
        expect($buffer)->toContain('Logging:');
        expect($buffer)->toContain('Webhook Events:');
        expect($buffer)->toContain('Available payment methods: 1');
        expect($buffer)->toContain('Accounts retrieved: 1');
    });

    it('checks only CHIP Collect API with --collect flag', function (): void {
        Http::fake([
            '*' => Http::response(['payment_methods' => []], 200),
        ]);

        $this->artisan(ChipHealthCheckCommand::class, ['--collect' => true])
            ->expectsOutputToContain('Checking CHIP Collect API')
            ->doesntExpectOutputToContain('Checking CHIP Send API')
            ->assertExitCode(0);
    });

    it('checks only CHIP Send API with --send flag', function (): void {
        Http::fake([
            '*' => Http::response(['accounts' => []], 200),
        ]);

        $this->artisan(ChipHealthCheckCommand::class, ['--send' => true])
            ->doesntExpectOutputToContain('Checking CHIP Collect API')
            ->expectsOutputToContain('Checking CHIP Send API')
            ->assertExitCode(0);
    });

    it('reports configuration issues for CHIP Collect API', function (): void {
        config()->set('chip.collect.brand_id', '');

        Http::fake([
            '*' => Http::response(['payment_methods' => []], 200),
        ]);

        $this->artisan(ChipHealthCheckCommand::class)
            ->expectsOutputToContain('Brand ID not configured')
            ->assertExitCode(1);
    });

    it('reports configuration issues for CHIP Send API', function (): void {
        config()->set('chip.send.api_key', '');

        Http::fake([
            '*' => Http::response(['accounts' => []], 200),
        ]);

        $this->artisan(ChipHealthCheckCommand::class, ['--send' => true])
            ->expectsOutputToContain('API Key not configured')
            ->assertExitCode(1);
    });

    it('returns exit code 1 when CHIP Collect API is unreachable', function (): void {
        Http::fake([
            '*/payment_methods/*' => Http::response(['error' => 'Unauthorized'], 401),
            '*/payment_methods' => Http::response(['error' => 'Unauthorized'], 401),
            '*/accounts' => Http::response(['accounts' => []], 200),
        ]);

        $this->artisan(ChipHealthCheckCommand::class)
            ->expectsOutputToContain('❌')
            ->assertExitCode(1);
    });

    it('returns exit code 1 when CHIP Send API is unreachable', function (): void {
        Http::fake([
            '*/payment_methods/*' => Http::response(['payment_methods' => []], 200),
            '*/payment_methods' => Http::response(['payment_methods' => []], 200),
            '*/accounts' => Http::response(['error' => 'Unauthorized'], 401),
        ]);

        $this->artisan(ChipHealthCheckCommand::class)
            ->expectsOutputToContain('❌')
            ->assertExitCode(1);
    });

    it('shows all systems operational when all checks pass', function (): void {
        Http::fake([
            '*' => Http::response(['payment_methods' => [], 'accounts' => []], 200),
        ]);

        $this->artisan(ChipHealthCheckCommand::class)
            ->expectsOutput('✅ All systems operational')
            ->assertExitCode(0);
    });
});
