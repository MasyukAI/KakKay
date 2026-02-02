<?php

declare(strict_types=1);

namespace Database\Seeders;

use AIArmada\Inventory\Models\InventoryLevel;
use AIArmada\Inventory\Models\InventoryLocation;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class InventorySeeder extends Seeder
{
    /**
     * Create stock records for all existing products.
     */
    public function run(): void
    {
        $location = InventoryLocation::firstOrCreate(
            ['code' => 'MAIN'],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Main Warehouse',
                'code' => 'MAIN',
                'line1' => '24, Jalan Pakis 1, Taman Fern Grove',
                'city' => 'Cheras',
                'state' => 'Selangor',
                'postcode' => '43200',
                'country' => 'MY',
                'is_active' => true,
                'priority' => 1,
            ]
        );

        $this->command->info("Location '{$location->name}' ready (ID: {$location->id})");

        $products = Product::all();
        $created = 0;
        $skipped = 0;

        foreach ($products as $product) {
            $exists = InventoryLevel::query()
                ->where('inventoryable_type', Product::class)
                ->where('inventoryable_id', $product->id)
                ->where('location_id', $location->id)
                ->exists();

            if ($exists) {
                $skipped++;

                continue;
            }

            InventoryLevel::create([
                'id' => Str::uuid()->toString(),
                'inventoryable_type' => Product::class,
                'inventoryable_id' => $product->id,
                'location_id' => $location->id,
                'quantity_on_hand' => 100,
                'quantity_reserved' => 0,
                'reorder_point' => 10,
                'safety_stock' => 5,
                'max_stock' => 500,
                'unit_of_measure' => 'pcs',
                'unit_conversion_factor' => 1.0,
            ]);

            $created++;
        }

        $this->command->info("Inventory levels: {$created} created, {$skipped} skipped");
    }
}
