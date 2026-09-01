<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Menyusun data 'seo' yang dipakai bersama oleh resources/views/app.blade.php
 * (render server-side, terlihat crawler tanpa JS/WhatsApp-Facebook link
 * preview) dan resources/js/Components/Seo.vue (update saat navigasi SPA).
 * Judul disusun sekali di sini agar keduanya selalu konsisten.
 */
class SeoMeta
{
    /**
     * @return array{title: string, description: ?string, image: ?string}
     */
    public static function make(string $title, ?string $description = null, ?string $image = null): array
    {
        $storeName = Setting::get('store_name', 'Toko Online');

        return [
            'title' => "{$title} — {$storeName}",
            'description' => $description,
            'image' => $image,
        ];
    }
}
