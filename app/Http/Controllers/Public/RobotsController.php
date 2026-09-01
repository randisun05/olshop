<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    /**
     * robots.txt dinamis (bukan file statis) agar URL Sitemap selalu memakai
     * APP_URL yang benar di tiap environment, dan area privat (admin/akun/
     * checkout/dst) tidak pernah ikut ter-crawl mesin pencari.
     */
    public function __invoke(): Response
    {
        $disallowed = ['/admin', '/akun', '/akun-saya', '/auth', '/checkout', '/keranjang', '/pesanan', '/wishlist'];

        $lines = ['User-agent: *'];
        foreach ($disallowed as $path) {
            $lines[] = "Disallow: {$path}";
        }
        $lines[] = '';
        $lines[] = 'Sitemap: '.route('sitemap');

        return response(implode("\n", $lines), 200)->header('Content-Type', 'text/plain');
    }
}
