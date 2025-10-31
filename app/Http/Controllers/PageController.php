<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Page;

final class PageController extends Controller
{
    /**
     * Display the specified page by slug.
     */
    public function show(string $slug): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        $page = Page::where('slug', $slug)
            // ->where('is_published', true)
            ->first();

        if (! $page) {
            abort(404);
        }

        return view('pages.'.$slug, compact('page'));
    }
}
