<?php

namespace Tests\Feature\Storefront;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_shows_only_active_products(): void
    {
        $active = Product::factory()->create(['name' => 'Produk Aktif', 'is_active' => true]);
        Product::factory()->create(['name' => 'Produk Nonaktif', 'is_active' => false]);

        $response = $this->get(route('catalog'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Storefront/Catalog')
            ->where('products.data.0.id', $active->id)
            ->has('products.data', 1)
        );
    }

    public function test_product_detail_page_shows_variants(): void
    {
        $product = Product::factory()->create(['is_active' => true]);

        $response = $this->get(route('catalog.show', $product->slug));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Storefront/ProductDetail')
            ->where('product.slug', $product->slug)
            ->has('product.variants', 1)
        );
    }

    public function test_inactive_product_detail_returns_404(): void
    {
        $product = Product::factory()->create(['is_active' => false]);

        $response = $this->get(route('catalog.show', $product->slug));

        $response->assertNotFound();
    }
}
