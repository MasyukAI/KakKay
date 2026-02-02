<?php

declare(strict_types=1);

namespace Database\Seeders;

use AIArmada\Promotions\Enums\PromotionType;
use AIArmada\Promotions\Models\Promotion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class PromotionSeeder extends Seeder
{
    /**
     * Create test promotions.
     */
    public function run(): void
    {
        $promotion = Promotion::firstOrCreate(
            ['code' => 'SAVE10'],
            [
                'id' => Str::uuid()->toString(),
                'name' => '10% off orders over RM50',
                'code' => 'SAVE10',
                'description' => 'Get 10% discount on orders over RM50',
                'type' => PromotionType::Percentage,
                'discount_value' => 1000, // 10% in basis points (10.00%)
                'priority' => 10,
                'is_stackable' => true,
                'is_active' => true,
                'min_purchase_amount' => 5000, // RM50 minimum in cents
                'starts_at' => now(),
                'ends_at' => now()->addYear(),
            ]
        );

        $this->command->info("Promotion '{$promotion->name}' ready (Code: {$promotion->code})");
    }
}
