<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PricingSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['group' => 'pricing', 'name' => 'defaultCurrency', 'locked' => false, 'payload' => json_encode('MYR')],
            ['group' => 'pricing', 'name' => 'decimalPlaces', 'locked' => false, 'payload' => json_encode(2)],
            ['group' => 'pricing', 'name' => 'roundingMode', 'locked' => false, 'payload' => json_encode('half_up')],
            ['group' => 'pricing', 'name' => 'pricesIncludeTax', 'locked' => false, 'payload' => json_encode(false)],
            ['group' => 'pricing', 'name' => 'minimumOrderValue', 'locked' => false, 'payload' => json_encode(0)],
            ['group' => 'pricing', 'name' => 'maximumOrderValue', 'locked' => false, 'payload' => json_encode(0)],
            ['group' => 'pricing', 'name' => 'promotionalPricingEnabled', 'locked' => false, 'payload' => json_encode(true)],
            ['group' => 'pricing', 'name' => 'tieredPricingEnabled', 'locked' => false, 'payload' => json_encode(true)],
            ['group' => 'pricing', 'name' => 'customerGroupPricingEnabled', 'locked' => false, 'payload' => json_encode(false)],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['group' => $setting['group'], 'name' => $setting['name']],
                array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
