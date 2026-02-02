<?php

declare(strict_types=1);

namespace Database\Seeders;

use AIArmada\Pricing\Models\Price;
use AIArmada\Pricing\Models\PriceList;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class PricingSeeder extends Seeder
{
    /**
     * Create default price list and migrate existing product prices.
     */
    public function run(): void
    {
        $priceList = PriceList::firstOrCreate(
            ['slug' => 'retail'],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Retail',
                'slug' => 'retail',
                'description' => 'Default retail price list',
                'currency' => 'MYR',
                'priority' => 0,
                'is_default' => true,
                'is_active' => true,
            ]
        );

        $this->command->info("Price list '{$priceList->name}' ready (ID: {$priceList->id})");

        $products = Product::query()
            ->whereNotNull('price')
            ->where('price', '>', 0)
            ->get();

        $created = 0;
        $skipped = 0;

        foreach ($products as $product) {
            $exists = Price::query()
                ->where('price_list_id', $priceList->id)
                ->where('priceable_type', Product::class)
                ->where('priceable_id', $product->id)
                ->exists();

            if ($exists) {
                $skipped++;

                continue;
            }

            Price::create([
                'id' => Str::uuid()->toString(),
                'price_list_id' => $priceList->id,
                'priceable_type' => Product::class,
                'priceable_id' => $product->id,
                'amount' => $product->price,
                'compare_amount' => $product->compare_price,
                'currency' => $product->currency ?? 'MYR',
                'min_quantity' => 1,
            ]);

            $created++;
        }

        $this->command->info("Prices migrated: {$created} created, {$skipped} skipped");
    }
}
