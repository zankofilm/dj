<?php

namespace App\Http\Controllers;

use App\Models\StaticPage;
use Illuminate\View\View;

class StaticPageController extends Controller
{
    public function show(string $slug): View
    {
        $page = StaticPage::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->firstOrFail();

        return view('pages.show', compact('page'));
    }
}
