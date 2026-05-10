<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class StorefrontController extends Controller
{
    public function books(): View
    {
        $products = $this->activeProducts();
        $bundleProducts = $products->take(4);

        return view('pages.books', [
            'products' => $products,
            'bundleProducts' => $bundleProducts,
            'bundleTotal' => (int) $bundleProducts->sum('price'),
            'bundleCompareTotal' => (int) $bundleProducts->sum(fn (Product $product): int => (int) ($product->compare_price ?? ($product->price + 900))),
        ]);
    }

    public function consultation(): View
    {
        return view('pages.consultation', [
            'highlightedProduct' => $this->activeProducts()->firstWhere('slug', 'cara-bercinta') ?? $this->activeProducts()->first(),
        ]);
    }

    public function kkdi(): View
    {
        return view('pages.kkdi');
    }

    /**
     * @return Collection<int, Product>
     */
    private function activeProducts(): Collection
    {
        /** @var Collection<int, Product> $products */
        $products = Cache::remember('storefront.active-products', now()->addMinutes(10), function (): Collection {
            return Product::query()
                ->select(['id', 'name', 'slug', 'description', 'price', 'compare_price', 'is_featured', 'status'])
                ->where('status', 'active')
                ->orderByDesc('is_featured')
                ->orderBy('name')
                ->get();
        });

        return $products;
    }
}
