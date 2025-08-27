<?php

namespace App\Http\Controllers;

use App\Models\Page;

class PageController extends Controller
{
    /**
     * Display the specified page by slug.
     */
    public function show(string $slug)
    {
        return view('pages.'.$slug);
    }

    //    public function show(string $slug)
    //    {
    //        $page = Page::where('slug', $slug)
    //            // ->where('is_published', true)
    //            ->first();
    //
    //        if (!$page) {
    //            return redirect('/');
    //        }
    //
    //        return view('pages.' . $slug, compact('page'));
    //    }
}
