<?php

declare(strict_types=1);

namespace App\Livewire;

use AIArmada\Cart\Facades\Cart as CartFacade;
use AIArmada\Products\Enums\ProductStatus;
use AIArmada\Products\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.home')]
final class Home extends Component
{
    public function render(): \Illuminate\Contracts\View\View
    {
        $allProducts = Cache::remember('home.products', now()->addMinutes(5), function () {
            return Product::query()
                ->select(['id', 'name', 'slug', 'description', 'price', 'is_featured', 'status', 'updated_at'])
                ->where(function ($query) {
                    $query->where('status', ProductStatus::Active)
                        ->orWhere('is_featured', true);
                })
                ->orderByDesc('is_featured')
                ->get();
        });

        $featuredProduct = $allProducts->firstWhere('is_featured', true)
            ?? $allProducts->first();

        $products = $allProducts->reject(fn (Product $product): bool => $featuredProduct?->is($product) ?? false);

        $featuredCoverPath = $featuredProduct
            ? sprintf('images/cover/%s.webp', $featuredProduct->slug)
            : null;
        $hasFeaturedCover = false;

        if ($featuredCoverPath && $featuredProduct) {
            $cacheKey = sprintf(
                'home.featured_cover_exists.%s.%s',
                $featuredProduct->getKey(),
                $featuredProduct->updated_at?->timestamp ?? '0'
            );

            $hasFeaturedCover = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($featuredCoverPath) {
                return Storage::disk('public')->exists($featuredCoverPath);
            });
        }

        return view('livewire.home', [
            'featuredProduct' => $featuredProduct,
            'featuredCoverPath' => $featuredCoverPath,
            'hasFeaturedCover' => $hasFeaturedCover,
            'products' => $products,
            'cartQuantity' => CartFacade::getTotalQuantity(),
        ]);
    }
}
