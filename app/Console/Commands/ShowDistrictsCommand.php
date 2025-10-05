<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\District;
use Illuminate\Console\Command;

final class ShowDistrictsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'district:show {state_id? : The state ID to filter districts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show districts, optionally filtered by state';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $stateId = $this->argument('state_id');

        if ($stateId) {
            $this->info("Districts for state ID: {$stateId}");
            $districts = District::getByState($stateId);
        } else {
            $this->info('All districts:');
            $districts = District::orderBy('name')->get();
        }

        if ($districts->isEmpty()) {
            $this->warn('No districts found.');

            return;
        }

        $this->table(
            ['ID', 'State ID', 'Name', 'Code'],
            $districts->map(function ($district) {
                return [
                    $district->id,
                    $district->state_id,
                    $district->name,
                    $district->code_3,
                ];
            })->toArray()
        );

        $this->info("Total: {$districts->count()} districts");
    }
}
