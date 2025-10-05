<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Component;
use MasyukAI\Cart\Facades\Cart as CartFacade;

#[Layout('components.layouts.home')]
final class Home extends Component
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
            'featuredProduct' => $featuredProduct,
            'products' => $products,
            'cartQuantity' => CartFacade::getTotalQuantity(),
        ]);
    }
}
