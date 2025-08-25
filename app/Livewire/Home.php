<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;

class Home extends Component
{
    public function render()
    {
        $product = Product::query()->where('is_featured', true)->first();

        $products = Product::query()
                        ->where('is_featured', false)
                        ->where('is_active', true)->get();

        return view('livewire.home', [
            'featuredImageUrl' => optional(
                    $product->getMedia('product-image-main')->first()
                )?->getUrl(),
            'featuredProductName' => $product->name,
            'featuredProductDescription' => $product->description,
            'products' => $products,
        ]);
    }
}
