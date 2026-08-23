<?php

namespace Tests\Feature\Admin;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_product_with_variants_and_image(): void
    {
        $admin = $this->createAdminUser();
        $category = Category::factory()->create();
        $attribute = Attribute::create(['name' => 'Warna']);
        $merah = $attribute->values()->create(['value' => 'Merah']);
        $biru = $attribute->values()->create(['value' => 'Biru']);

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Kaos Test',
            'slug' => 'kaos-test',
            'category_id' => $category->id,
            'description' => 'Deskripsi',
            'weight' => 200,
            'is_active' => true,
            'is_featured' => false,
            'images' => [UploadedFile::fake()->image('kaos.jpg', 300, 300)],
            'variants' => [
                ['sku' => '', 'price' => 89000, 'stock' => 10, 'attribute_value_ids' => [$merah->id]],
                ['sku' => '', 'price' => 89000, 'stock' => 5, 'attribute_value_ids' => [$biru->id]],
            ],
        ]);

        $product = Product::where('slug', 'kaos-test')->first();

        $response->assertRedirect(route('admin.products.edit', $product));
        $this->assertNotNull($product);
        $this->assertCount(2, $product->variants);
        $this->assertCount(1, $product->images);
    }

    public function test_product_without_any_variant_attributes_can_be_created(): void
    {
        $admin = $this->createAdminUser();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Produk Sederhana',
            'slug' => 'produk-sederhana',
            'category_id' => $category->id,
            'weight' => 100,
            'is_active' => true,
            'variants' => [
                ['sku' => '', 'price' => 50000, 'stock' => 3, 'attribute_value_ids' => []],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['slug' => 'produk-sederhana']);
    }

    public function test_deleting_product_removes_its_variants(): void
    {
        $admin = $this->createAdminUser();
        $product = Product::factory()->create();
        $variantId = $product->variants()->first()->id;

        $response = $this->actingAs($admin)->delete(route('admin.products.destroy', $product));

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseMissing('product_variants', ['id' => $variantId]);
    }
}
