<?php

use App\Models\Product;
use App\Models\Category;
use Livewire\Volt\Volt;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can render', function () {
    // Create test category and products
    $category = Category::factory()->create();
    Product::factory()->count(3)->create([
        'is_active' => true,
        'category_id' => $category->id
    ]);
    
    $component = Volt::test('cart');

    $component->assertSee('Troli Masih Kosong'); // Cart should be empty initially
});
