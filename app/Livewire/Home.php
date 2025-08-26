<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;

class Home extends Component
{
    public function render()
    {
        $allProducts = Product::query()
            ->where('is_active', true)
            ->orWhere('is_featured', true)
            ->orderByDesc('is_featured')
            ->get();

        $featuredProduct = $allProducts->firstWhere('is_featured', true);

        $products = $allProducts->where('is_featured', false);

        return view('livewire.home', [
            'featuredImageUrl' => optional($featuredProduct?->getMedia('product-image-main')->first())?->getUrl(),
            'featuredProductName' => $featuredProduct?->name,
            'featuredProductDescription' => $featuredProduct?->description,
            'products' => $products,
        ]);
    }
}
