<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Sitemap XML sederhana untuk mesin pencari: beranda, katalog, produk
     * aktif, dan halaman statis aktif. Dibuat sebagai rute dinamis (bukan
     * file statis) agar URL selalu memakai APP_URL yang benar di tiap
     * environment (dev/staging/produksi).
     */
    public function __invoke(): Response
    {
        $urls = collect([
            ['loc' => route('home'), 'priority' => '1.0'],
            ['loc' => route('catalog'), 'priority' => '0.9'],
        ]);

        Product::where('is_active', true)
            ->orderByDesc('updated_at')
            ->chunk(200, function ($products) use ($urls) {
                foreach ($products as $product) {
                    $urls->push([
                        'loc' => route('catalog.show', $product->slug),
                        'lastmod' => $product->updated_at->toAtomString(),
                        'priority' => '0.8',
                    ]);
                }
            });

        Page::where('is_active', true)->get()->each(function (Page $page) use ($urls) {
            $urls->push([
                'loc' => route('page.show', $page->slug),
                'lastmod' => $page->updated_at->toAtomString(),
                'priority' => '0.5',
            ]);
        });

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
