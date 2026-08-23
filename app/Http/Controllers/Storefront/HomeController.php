<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Storefront/Home', [
            'banners' => Banner::where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'title', 'image', 'link_url']),
        ]);
    }
}
