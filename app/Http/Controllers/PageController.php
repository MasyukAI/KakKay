<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;

final class PageController extends Controller
{
    /**
     * Display the specified product page by slug.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)->first();

        if (! $product) {
            abort(404);
        }

        /** @var view-string $view */
        $view = 'products.show';

        return view($view, compact('product'));
    }
}
