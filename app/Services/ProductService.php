<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class ProductService
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  UploadedFile[]  $images
     * @param  array<int, array{sku: ?string, price: float, stock: int, attribute_value_ids: int[]}>  $variants
     */
    public function create(array $attributes, array $images, array $variants): Product
    {
        return DB::transaction(function () use ($attributes, $images, $variants) {
            $product = Product::create($attributes);

            $this->storeImages($product, $images);
            $this->syncVariants($product, $variants);

            return $product;
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  UploadedFile[]  $newImages
     * @param  int[]  $deleteImageIds
     * @param  array<int, array{id: ?int, sku: ?string, price: float, stock: int, attribute_value_ids: int[]}>  $variants
     */
    public function update(
        Product $product,
        array $attributes,
        array $newImages,
        array $deleteImageIds,
        array $variants
    ): Product {
        return DB::transaction(function () use ($product, $attributes, $newImages, $deleteImageIds, $variants) {
            $product->update($attributes);

            $this->deleteImages($product, $deleteImageIds);
            $this->storeImages($product, $newImages);
            $this->syncVariants($product, $variants);

            return $product;
        });
    }

    public function delete(Product $product): void
    {
        DB::transaction(function () use ($product) {
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->path);
            }

            $product->delete();
        });
    }

    /**
     * @param  UploadedFile[]  $images
     */
    private function storeImages(Product $product, array $images): void
    {
        $nextOrder = (int) $product->images()->max('sort_order') + 1;

        foreach ($images as $index => $file) {
            $encoded = Image::decode($file)->scaleDown(width: 1200)->encodeUsingFileExtension('jpg', quality: 82);
            $path = 'products/'.$product->id.'/'.Str::random(20).'.jpg';

            Storage::disk('public')->put($path, (string) $encoded);

            ProductImage::create([
                'product_id' => $product->id,
                'path' => $path,
                'sort_order' => $nextOrder + $index,
            ]);
        }
    }

    /**
     * @param  int[]  $imageIds
     */
    private function deleteImages(Product $product, array $imageIds): void
    {
        if (empty($imageIds)) {
            return;
        }

        $images = $product->images()->whereIn('id', $imageIds)->get();

        foreach ($images as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }
    }

    /**
     * @param  array<int, array{id?: ?int, sku: ?string, price: float, stock: int, attribute_value_ids: int[]}>  $variants
     */
    private function syncVariants(Product $product, array $variants): void
    {
        $keptVariantIds = [];

        foreach ($variants as $variantData) {
            $variantId = $variantData['id'] ?? null;
            $sku = ($variantData['sku'] ?? '') ?: $this->generateSku($product);
            $attributeValueIds = $variantData['attribute_value_ids'] ?? [];

            if ($variantId && $variant = $product->variants()->find($variantId)) {
                $variant->update([
                    'sku' => $sku,
                    'price' => $variantData['price'],
                    'stock' => $variantData['stock'],
                ]);
            } else {
                $variant = $product->variants()->create([
                    'sku' => $sku,
                    'price' => $variantData['price'],
                    'stock' => $variantData['stock'],
                ]);
            }

            $variant->attributeValues()->sync($attributeValueIds);
            $keptVariantIds[] = $variant->id;
        }

        $product->variants()->whereNotIn('id', $keptVariantIds)->delete();
    }

    private function generateSku(Product $product): string
    {
        return Str::upper(Str::slug($product->name, '')).'-'.Str::upper(Str::random(6));
    }
}
