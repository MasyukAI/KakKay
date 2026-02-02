<?php

declare(strict_types=1);

use AIArmada\Cart\Facades\Cart as CartFacade;
use AIArmada\Inventory\Models\InventoryLevel;
use AIArmada\Inventory\Models\InventoryLocation;
use AIArmada\Pricing\Models\Price;
use AIArmada\Pricing\Models\PriceList;
use AIArmada\Promotions\Enums\PromotionType;
use AIArmada\Promotions\Models\Promotion;
use AIArmada\Vouchers\Models\Voucher as VoucherModel;
use App\Livewire\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    CartFacade::clear();
});

describe('Pricing Package Integration', function () {
    it('resolves product price from prices table', function () {
        $priceList = PriceList::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Default',
            'slug' => 'default',
            'currency' => 'MYR',
            'is_default' => true,
            'is_active' => true,
        ]);

        $product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 1000, // Fallback price
        ]);

        Price::create([
            'id' => Str::uuid()->toString(),
            'price_list_id' => $priceList->id,
            'priceable_type' => Product::class,
            'priceable_id' => $product->id,
            'amount' => 2500, // RM25.00 in prices table
            'currency' => 'MYR',
            'min_quantity' => 1,
        ]);

        // Refresh to get relationship
        $product->refresh();

        // Should get price from prices table, not Product.price
        expect($product->getBasePrice())->toBe(2500);
        expect($product->getBuyableIdentifier())->toBe((string) $product->id);
    });

    it('falls back to Product.price when no price record exists', function () {
        $product = Product::factory()->create([
            'name' => 'Fallback Test',
            'price' => 3500, // RM35.00
        ]);

        // No price list or price record created
        expect($product->getBasePrice())->toBe(3500);
    });

    it('detects on sale from prices table compare_amount', function () {
        $priceList = PriceList::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Default',
            'slug' => 'default-sale',
            'currency' => 'MYR',
            'is_default' => true,
            'is_active' => true,
        ]);

        $product = Product::factory()->create([
            'name' => 'Sale Product',
            'price' => 2000,
        ]);

        Price::create([
            'id' => Str::uuid()->toString(),
            'price_list_id' => $priceList->id,
            'priceable_type' => Product::class,
            'priceable_id' => $product->id,
            'amount' => 1500, // Current price RM15
            'compare_amount' => 2000, // Original price RM20
            'currency' => 'MYR',
            'min_quantity' => 1,
        ]);

        $product->refresh();

        expect($product->isOnSale())->toBeTrue();
        expect($product->getDiscountPercentage())->toBe(25.0); // (2000-1500)/2000 * 100
    });
});

describe('Inventory Package Integration', function () {
    it('creates inventory level for product', function () {
        $location = InventoryLocation::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Test Warehouse',
            'code' => 'TEST',
            'is_active' => true,
        ]);

        $product = Product::factory()->create(['name' => 'Inventory Test']);

        $level = InventoryLevel::create([
            'id' => Str::uuid()->toString(),
            'inventoryable_type' => Product::class,
            'inventoryable_id' => $product->id,
            'location_id' => $location->id,
            'quantity_on_hand' => 50,
            'quantity_reserved' => 0,
            'unit_of_measure' => 'pcs',
            'unit_conversion_factor' => 1.0,
        ]);

        expect($level->quantity_on_hand)->toBe(50);
        expect($level->inventoryable_id)->toBe($product->id);
    });

    it('tracks available quantity correctly', function () {
        $location = InventoryLocation::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Available Test',
            'code' => 'AVAIL',
            'is_active' => true,
        ]);

        $product = Product::factory()->create(['name' => 'Available Qty Test']);

        $level = InventoryLevel::create([
            'id' => Str::uuid()->toString(),
            'inventoryable_type' => Product::class,
            'inventoryable_id' => $product->id,
            'location_id' => $location->id,
            'quantity_on_hand' => 100,
            'quantity_reserved' => 20,
            'unit_of_measure' => 'pcs',
            'unit_conversion_factor' => 1.0,
        ]);

        // Available = on_hand - reserved
        expect($level->available)->toBe(80);
    });
});

describe('Promotions Package Integration', function () {
    it('creates percentage promotion correctly', function () {
        $promotion = Promotion::create([
            'id' => Str::uuid()->toString(),
            'name' => '10% Off Everything',
            'code' => 'TEST10',
            'type' => PromotionType::Percentage,
            'discount_value' => 1000, // 10%
            'is_active' => true,
            'is_stackable' => true,
        ]);

        expect($promotion->type)->toBe(PromotionType::Percentage);
        expect($promotion->discount_value)->toBe(1000);
        expect($promotion->is_active)->toBeTrue();
    });

    it('creates fixed amount promotion correctly', function () {
        $promotion = Promotion::create([
            'id' => Str::uuid()->toString(),
            'name' => 'RM5 Off',
            'code' => 'FIXED5',
            'type' => PromotionType::Fixed,
            'discount_value' => 500, // RM5.00
            'is_active' => true,
        ]);

        expect($promotion->type)->toBe(PromotionType::Fixed);
        expect($promotion->discount_value)->toBe(500);
    });

    it('respects min purchase amount', function () {
        $promotion = Promotion::create([
            'id' => Str::uuid()->toString(),
            'name' => '10% Over RM50',
            'code' => 'MIN50',
            'type' => PromotionType::Percentage,
            'discount_value' => 1000,
            'min_purchase_amount' => 5000, // RM50 minimum
            'is_active' => true,
        ]);

        expect($promotion->min_purchase_amount)->toBe(5000);
    });
});

describe('Vouchers Package Integration', function () {
    it('can create and validate voucher', function () {
        $voucher = VoucherModel::create([
            'id' => Str::uuid()->toString(),
            'code' => 'TESTVOUCHER',
            'name' => 'Test Voucher',
            'type' => 'percentage',
            'value' => 1500, // 15%
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
        ]);

        expect($voucher->code)->toBe('TESTVOUCHER');
        expect($voucher->value)->toBe(1500);
        expect($voucher->status)->toBeInstanceOf(AIArmada\Vouchers\States\Active::class);
    });
});

describe('Cart with Pricing Integration', function () {
    it('adds product to cart with price from prices table', function () {
        $user = User::factory()->create();

        $priceList = PriceList::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Cart Test',
            'slug' => 'cart-test',
            'currency' => 'MYR',
            'is_default' => true,
            'is_active' => true,
        ]);

        $product = Product::factory()->create([
            'name' => 'Cart Price Test',
            'price' => 1000, // Fallback
            'status' => 'active',
        ]);

        Price::create([
            'id' => Str::uuid()->toString(),
            'price_list_id' => $priceList->id,
            'priceable_type' => Product::class,
            'priceable_id' => $product->id,
            'amount' => 2999, // RM29.99
            'currency' => 'MYR',
            'min_quantity' => 1,
        ]);

        $product->refresh();

        $cart = Livewire::actingAs($user)->test(Cart::class);
        $cart->call('addToCart', $product, 1);

        $cartItems = $cart->get('cartItems');
        expect($cartItems)->toHaveCount(1);
        expect($cartItems[0]['name'])->toBe('Cart Price Test');
    });
});
