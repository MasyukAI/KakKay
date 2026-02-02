<?php

declare(strict_types=1);

namespace App\Livewire;

use AIArmada\Cart\Facades\Cart as CartFacade;
use AIArmada\Products\Enums\ProductStatus;
use AIArmada\Products\Models\Product;
use Illuminate\Support\Facades\Storage;
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

        $featuredProduct = $allProducts->firstWhere('is_featured', true)
            ?? $allProducts->first();

        $products = $allProducts->reject(fn (Product $product): bool => $featuredProduct?->is($product) ?? false);

        $featuredCoverPath = $featuredProduct
            ? sprintf('images/cover/%s.webp', $featuredProduct->slug)
            : null;
        $hasFeaturedCover = $featuredCoverPath
            ? Storage::disk('public')->exists($featuredCoverPath)
            : false;

        return view('livewire.home', [
            'featuredProduct' => $featuredProduct,
            'featuredCoverPath' => $featuredCoverPath,
            'hasFeaturedCover' => $hasFeaturedCover,
            'products' => $products,
            'cartQuantity' => CartFacade::getTotalQuantity(),
        ]);
    }
}
