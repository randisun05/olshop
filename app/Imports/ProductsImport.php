<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Import produk sederhana: satu varian per baris/produk (tanpa kombinasi
 * atribut seperti ukuran/warna). Produk dengan banyak varian tetap perlu
 * ditambahkan lewat form admin biasa.
 */
class ProductsImport implements ToCollection, WithHeadingRow
{
    public int $created = 0;

    public int $updated = 0;

    /** @var array<int, string> */
    public array $rowErrors = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            $validator = Validator::make($row->toArray(), [
                'nama' => ['required', 'string', 'max:255'],
                'kategori' => ['required', 'string'],
                'harga' => ['required', 'numeric', 'min:0'],
                'stok' => ['required', 'integer', 'min:0'],
            ]);

            if ($validator->fails()) {
                $this->rowErrors[] = "Baris {$rowNumber}: ".$validator->errors()->first();

                continue;
            }

            $category = Category::where('name', trim((string) $row['kategori']))->first();

            if (! $category) {
                $this->rowErrors[] = "Baris {$rowNumber}: kategori \"{$row['kategori']}\" tidak ditemukan.";

                continue;
            }

            $brandId = null;

            if (! empty($row['brand'])) {
                $brand = Brand::where('name', trim((string) $row['brand']))->first();

                if (! $brand) {
                    $this->rowErrors[] = "Baris {$rowNumber}: brand \"{$row['brand']}\" tidak ditemukan.";

                    continue;
                }

                $brandId = $brand->id;
            }

            $slug = Str::slug($row['nama']);
            $product = Product::where('slug', $slug)->first();
            $existingVariant = $product?->variants()->first();

            $sku = ! empty($row['sku']) ? trim((string) $row['sku']) : null;

            if ($sku && ProductVariant::where('sku', $sku)->where('id', '!=', $existingVariant?->id)->exists()) {
                $this->rowErrors[] = "Baris {$rowNumber}: SKU \"{$sku}\" sudah dipakai varian lain.";

                continue;
            }

            $productAttributes = [
                'category_id' => $category->id,
                'brand_id' => $brandId,
                'name' => trim((string) $row['nama']),
                'slug' => $slug,
                'description' => $row['deskripsi'] ?? null,
                'weight' => $row['berat'] ?? 0,
                'is_active' => true,
                'is_featured' => false,
            ];

            if ($product) {
                $product->update($productAttributes);
                $this->updated++;
            } else {
                $product = Product::create($productAttributes);
                $this->created++;
            }

            $variantAttributes = [
                'sku' => $sku ?? (Str::upper(Str::slug($product->name, '')).'-'.Str::upper(Str::random(6))),
                'price' => $row['harga'],
                'stock' => $row['stok'],
            ];

            if ($existingVariant) {
                $existingVariant->update($variantAttributes);
            } else {
                $product->variants()->create($variantAttributes);
            }
        }
    }
}
