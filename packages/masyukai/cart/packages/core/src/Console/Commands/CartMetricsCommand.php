<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Console\Commands;

use Illuminate\Console\Command;
use MasyukAI\Cart\Services\CartMetricsService;

class CartMetricsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cart:metrics 
                          {--clear : Clear all metrics}
                          {--json : Output as JSON}';

    /**
     * The console command description.
     */
    protected $description = 'Display cart performance and usage metrics';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $metricsService = app(CartMetricsService::class);

        if ($this->option('clear')) {
            $metricsService->clearMetrics();
            $this->info('All cart metrics have been cleared.');

            return self::SUCCESS;
        }

        $metrics = $metricsService->getMetricsSummary();

        if ($this->option('json')) {
            $json = json_encode($metrics, JSON_PRETTY_PRINT);
            $this->line($json !== false ? $json : 'Failed to encode metrics to JSON');

            return self::SUCCESS;
        }

        $this->displayMetrics($metrics);

        return self::SUCCESS;
    }

    /**
     * Display metrics in a formatted table
     */
    private function displayMetrics(array $metrics): void
    {
        $this->info('🛒 Cart Metrics Summary');
        $this->newLine();

        // Operations metrics
        $this->info('📊 Operations:');
        $this->table(
            ['Metric', 'Total', 'Today'],
            [
                ['Operations', $metrics['operations']['total'], $metrics['operations']['today']],
            ]
        );

        // Conflicts metrics
        $this->info('⚠️  Conflicts:');
        $conflictData = [
            ['Total Conflicts', $metrics['conflicts']['total'], $metrics['conflicts']['today']],
            ['Minor Conflicts', $metrics['conflicts']['minor'], '-'],
            ['Major Conflicts', $metrics['conflicts']['major'], '-'],
        ];
        $this->table(['Type', 'Total', 'Today'], $conflictData);

        // Conversion metrics
        $this->info('📈 Conversions & Abandonments:');
        $conversionData = [
            ['Conversions', $metrics['conversions']['total'], $metrics['conversions']['today']],
            ['Abandonments', $metrics['abandonments']['total'], $metrics['abandonments']['today']],
        ];
        $this->table(['Metric', 'Total', 'Today'], $conversionData);

        // Performance metrics
        if (! empty($metrics['performance'])) {
            $this->info('⚡ Performance (seconds):');
            $performanceData = [];
            foreach ($metrics['performance'] as $operation => $data) {
                if ($data) {
                    $performanceData[] = [
                        ucfirst($operation),
                        $data['avg'] ?? 'N/A',
                        $data['min'] ?? 'N/A',
                        $data['max'] ?? 'N/A',
                        $data['count'] ?? 'N/A',
                    ];
                }
            }

            if (! empty($performanceData)) {
                $this->table(['Operation', 'Avg', 'Min', 'Max', 'Count'], $performanceData);
            } else {
                $this->comment('No performance data available.');
            }
        }

        // Recommendations
        $this->newLine();
        $this->info('💡 Recommendations:');

        $conflictRate = $metrics['operations']['total'] > 0
            ? ($metrics['conflicts']['total'] / $metrics['operations']['total']) * 100
            : 0;

        if ($conflictRate > 5) {
            $this->warn("• High conflict rate: {$conflictRate}% - Consider optimizing concurrent access patterns");
        } elseif ($conflictRate > 1) {
            $this->comment("• Moderate conflict rate: {$conflictRate}% - Monitor for increased concurrency");
        } else {
            $this->info("• Low conflict rate: {$conflictRate}% - Good concurrency handling");
        }

        $conversionRate = $metrics['abandonments']['total'] > 0
            ? ($metrics['conversions']['total'] / ($metrics['conversions']['total'] + $metrics['abandonments']['total'])) * 100
            : 0;

        if ($conversionRate < 20) {
            $this->warn("• Low conversion rate: {$conversionRate}% - Consider cart abandonment strategies");
        } elseif ($conversionRate < 50) {
            $this->comment("• Moderate conversion rate: {$conversionRate}% - Room for improvement");
        } else {
            $this->info("• Good conversion rate: {$conversionRate}% - Cart performance is healthy");
        }
    }
}
