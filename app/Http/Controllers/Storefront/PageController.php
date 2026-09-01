<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Support\SeoMeta;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function show(string $slug): Response
    {
        $page = Page::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return Inertia::render('Storefront/Page', [
            'page' => $page->only('title', 'content'),
            'seo' => SeoMeta::make($page->title, Str::limit(strip_tags($page->content), 160)),
        ]);
    }
}
