<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    /**
     * Data contoh untuk memverifikasi alur katalog. Bukan data produksi.
     */
    public function run(): void
    {
        $pakaian = Category::create(['name' => 'Pakaian', 'slug' => 'pakaian']);
        Category::create(['name' => 'Kaos', 'slug' => 'kaos', 'parent_id' => $pakaian->id]);
        Category::create(['name' => 'Celana', 'slug' => 'celana', 'parent_id' => $pakaian->id]);

        $elektronik = Category::create(['name' => 'Elektronik', 'slug' => 'elektronik']);
        Category::create(['name' => 'Aksesoris HP', 'slug' => 'aksesoris-hp', 'parent_id' => $elektronik->id]);

        $rumahTangga = Category::create(['name' => 'Rumah Tangga', 'slug' => 'rumah-tangga']);

        $brandA = Brand::create(['name' => 'Brand A', 'slug' => 'brand-a']);
        $brandB = Brand::create(['name' => 'Brand B', 'slug' => 'brand-b']);

        $warna = Attribute::create(['name' => 'Warna']);
        $merah = $warna->values()->create(['value' => 'Merah']);
        $biru = $warna->values()->create(['value' => 'Biru']);
        $hitam = $warna->values()->create(['value' => 'Hitam']);

        $ukuran = Attribute::create(['name' => 'Ukuran']);
        $s = $ukuran->values()->create(['value' => 'S']);
        $m = $ukuran->values()->create(['value' => 'M']);
        $l = $ukuran->values()->create(['value' => 'L']);

        $kaos = Category::where('slug', 'kaos')->first();
        $product = Product::create([
            'category_id' => $kaos->id,
            'brand_id' => $brandA->id,
            'name' => 'Kaos Polos Combed 30s',
            'slug' => Str::slug('Kaos Polos Combed 30s'),
            'description' => 'Kaos polos bahan combed 30s, nyaman dan adem untuk sehari-hari.',
            'weight' => 200,
            'is_featured' => true,
        ]);
        foreach ([$merah, $biru, $hitam] as $warnaValue) {
            foreach ([$s, $m, $l] as $ukuranValue) {
                $variant = $product->variants()->create([
                    'sku' => 'KAOS-'.Str::upper(Str::random(6)),
                    'price' => 89000,
                    'stock' => rand(5, 30),
                ]);
                $variant->attributeValues()->attach([$warnaValue->id, $ukuranValue->id]);
            }
        }

        $celana = Category::where('slug', 'celana')->first();
        $product2 = Product::create([
            'category_id' => $celana->id,
            'brand_id' => $brandB->id,
            'name' => 'Celana Chino Slimfit',
            'slug' => Str::slug('Celana Chino Slimfit'),
            'description' => 'Celana chino slim fit, bahan stretch nyaman dipakai.',
            'weight' => 400,
        ]);
        foreach ([$s, $m, $l] as $ukuranValue) {
            $variant = $product2->variants()->create([
                'sku' => 'CHINO-'.Str::upper(Str::random(6)),
                'price' => 175000,
                'stock' => rand(5, 20),
            ]);
            $variant->attributeValues()->attach($ukuranValue->id);
        }

        $aksesoris = Category::where('slug', 'aksesoris-hp')->first();
        $product3 = Product::create([
            'category_id' => $aksesoris->id,
            'name' => 'Casing HP Silikon',
            'slug' => Str::slug('Casing HP Silikon'),
            'description' => 'Casing silikon anti-benturan.',
            'weight' => 50,
        ]);
        foreach ([$hitam, $biru] as $warnaValue) {
            $variant = $product3->variants()->create([
                'sku' => 'CASE-'.Str::upper(Str::random(6)),
                'price' => 35000,
                'stock' => rand(10, 50),
            ]);
            $variant->attributeValues()->attach($warnaValue->id);
        }

        $product4 = Product::create([
            'category_id' => $rumahTangga->id,
            'name' => 'Rak Serbaguna 3 Susun',
            'slug' => Str::slug('Rak Serbaguna 3 Susun'),
            'description' => 'Rak plastik serbaguna 3 susun, cocok untuk dapur atau kamar.',
            'weight' => 1200,
            'is_featured' => true,
        ]);
        $product4->variants()->create([
            'sku' => 'RAK-'.Str::upper(Str::random(6)),
            'price' => 129000,
            'stock' => 15,
        ]);
    }
}
