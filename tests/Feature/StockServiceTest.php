<?php

use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->stockService = new StockService;
    $this->admin = User::factory()->create(['name' => 'Admin User']);
    $this->product = Product::factory()->create([
        'name' => 'Test Product',
        'price' => 5000, // RM 50.00
    ]);
});

test('admin can add stock to product', function () {
    $transaction = $this->stockService->addStock(
        product: $this->product,
        quantity: 100,
        reason: 'restock',
        note: 'Initial stock from supplier',
        userId: $this->admin->id
    );

    expect($transaction)->toBeInstanceOf(StockTransaction::class)
        ->and($transaction->product_id)->toBe($this->product->id)
        ->and($transaction->user_id)->toBe($this->admin->id)
        ->and($transaction->quantity)->toBe(100)
        ->and($transaction->type)->toBe('in')
        ->and($transaction->reason)->toBe('restock')
        ->and($transaction->note)->toBe('Initial stock from supplier');
});

test('can get current stock level after multiple transactions', function () {
    // Add stock transactions
    $this->stockService->addStock($this->product, 100, userId: $this->admin->id);
    $this->stockService->addStock($this->product, 50, userId: $this->admin->id);
    $this->stockService->removeStock($this->product, 20, userId: $this->admin->id);

    $currentStock = $this->stockService->getCurrentStock($this->product);

    expect($currentStock)->toBe(130); // 100 + 50 - 20
});
