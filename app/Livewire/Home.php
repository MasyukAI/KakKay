<?php

declare(strict_types=1);

namespace App\Livewire;

use AIArmada\Cart\Facades\Cart as CartFacade;
use AIArmada\Products\Enums\ProductStatus;
use AIArmada\Products\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.home')]
final class Home extends Component
{
    public function render(): \Illuminate\Contracts\View\View
    {
        $allProducts = Product::query()
            ->where(function ($query) {
                $query->where('status', ProductStatus::Active)
                    ->orWhere('is_featured', true);
            })
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
