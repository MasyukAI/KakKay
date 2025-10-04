<?php

declare(strict_types=1);

namespace MasyukAI\Chip\Commands;

use Illuminate\Console\Command;
use MasyukAI\Chip\Services\ChipCollectService;
use MasyukAI\Chip\Services\ChipSendService;

class ChipHealthCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'chip:health
                            {--collect : Check only CHIP Collect API}
                            {--send : Check only CHIP Send API}';

    /**
     * The console command description.
     */
    protected $description = 'Check CHIP API connectivity and configuration';

    /**
     * Execute the console command.
     */
    public function handle(
        ChipCollectService $collectService,
        ChipSendService $sendService
    ): int {
        $this->info('🔍 CHIP API Health Check');
        $this->newLine();

        $checkCollect = ! $this->option('send') || $this->option('collect');
        $checkSend = ! $this->option('collect') || $this->option('send');

        $allHealthy = true;

        // Check CHIP Collect API
        if ($checkCollect) {
            $this->line('📦 <fg=cyan>Checking CHIP Collect API...</>');
            $collectHealthy = $this->checkCollectApi($collectService);
            if (! $collectHealthy) {
                $allHealthy = false;
            }
            $this->newLine();
        }

        // Check CHIP Send API
        if ($checkSend) {
            $this->line('💸 <fg=cyan>Checking CHIP Send API...</>');
            $sendHealthy = $this->checkSendApi($sendService);
            if (! $sendHealthy) {
                $allHealthy = false;
            }
            $this->newLine();
        }

        // Display configuration status
        if ($this->option('verbose')) {
            $this->displayConfiguration();
            $this->newLine();
        }

        // Final summary
        if ($allHealthy) {
            $this->info('✅ All systems operational');

            return self::SUCCESS;
        }

        $this->error('❌ Some systems are experiencing issues');

        return self::FAILURE;
    }

    /**
     * Check CHIP Collect API health
     */
    protected function checkCollectApi(ChipCollectService $service): bool
    {
        try {
            // Verify configuration
            if (! config('chip.collect.brand_id')) {
                $this->error('   ❌ Brand ID not configured');

                return false;
            }

            if (! config('chip.collect.api_key')) {
                $this->error('   ❌ API Key not configured');

                return false;
            }

            // Try to fetch payment methods as a connectivity test
            $methods = $service->getPaymentMethods();

            $this->info('   ✅ Connected');

            if ($this->option('verbose')) {
                $brandId = config('chip.collect.brand_id');
                $this->line("      Brand ID: {$brandId}");
                $this->line('      Available payment methods: '.count($methods));
            }

            return true;
        } catch (\Throwable $e) {
            $this->error('   ❌ Connection failed');

            if ($this->option('verbose')) {
                $this->line("      Error: {$e->getMessage()}");
            }

            return false;
        }
    }

    /**
     * Check CHIP Send API health
     */
    protected function checkSendApi(ChipSendService $service): bool
    {
        try {
            // Verify configuration
            if (! config('chip.send.api_key')) {
                $this->error('   ❌ API Key not configured');

                return false;
            }

            // Try to fetch available payout accounts as a connectivity test
            $accounts = $service->listAccounts();

            $this->info('   ✅ Connected');

            if ($this->option('verbose')) {
                $this->line('      Accounts retrieved: '.count($accounts));
            }

            return true;
        } catch (\Throwable $e) {
            $this->error('   ❌ Connection failed');

            if ($this->option('verbose')) {
                $this->line("      Error: {$e->getMessage()}");
            }

            return false;
        }
    }

    /**
     * Display current configuration
     */
    protected function displayConfiguration(): void
    {
        $this->line('⚙️  <fg=cyan>Configuration Status</>');

        // Environment
        $environment = config('chip.send.environment', 'sandbox');
        $this->line("   Environment: <fg=yellow>{$environment}</>");

        // Logging
        $loggingEnabled = config('chip.logging.log_requests', false);
        $status = $loggingEnabled ? '<fg=green>Enabled</>' : '<fg=red>Disabled</>';
        $this->line("   Logging: {$status}");

        // Webhooks
        $webhookEvents = config('chip.events.dispatch_webhook_events', true);
        $status = $webhookEvents ? '<fg=green>Enabled</>' : '<fg=red>Disabled</>';
        $this->line("   Webhook Events: {$status}");
    }
}
