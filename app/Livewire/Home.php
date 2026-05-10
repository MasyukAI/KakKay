<?php

declare(strict_types=1);

namespace App\Livewire;

use AIArmada\Cart\Facades\Cart as CartFacade;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.home')]
final class Home extends Component
{
    private const FEATURED_PRODUCT_SLUG = 'cara-bercinta';

    /**
     * @var list<string>
     */
    private const HOMEPAGE_PRODUCT_ORDER = [
        'cara-bercinta',
        'potensi-anak',
        'diari-healing',
        'tak-boleh-cakap',
        'kasihi-puteri',
        'kitab-kkdi',
        'sebab-terasa',
        'cara-cakap',
    ];

    public function render(): \Illuminate\Contracts\View\View
    {
        $allProducts = Cache::remember('home.products', now()->addMinutes(5), function () {
            return Product::query()
                ->select(['id', 'name', 'slug', 'description', 'price', 'is_featured', 'status', 'updated_at'])
                ->where(function ($query) {
                    $query->where('status', 'active')
                        ->orWhere('is_featured', true);
                })
                ->get();
        });

        $products = $this->orderHomepageProducts($allProducts);
        $featuredProduct = $products->firstWhere('slug', self::FEATURED_PRODUCT_SLUG)
            ?? $products->first();

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

    /**
     * @param  Collection<int, Product>  $products
     * @return Collection<int, Product>
     */
    private function orderHomepageProducts(Collection $products): Collection
    {
        $sortOrder = array_flip(self::HOMEPAGE_PRODUCT_ORDER);

        /** @var Collection<int, Product> $orderedProducts */
        $orderedProducts = $products
            ->sortBy(fn (Product $product): string => sprintf(
                '%04d-%s',
                $sortOrder[$product->slug] ?? 9999,
                mb_strtolower($product->name)
            ))
            ->values();

        return $orderedProducts;
    }
}
