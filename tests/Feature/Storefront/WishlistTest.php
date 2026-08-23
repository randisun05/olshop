<?php

namespace Tests\Feature\Storefront;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_toggling_wishlist(): void
    {
        $product = Product::factory()->create();

        $response = $this->post(route('wishlist.toggle', $product));

        $response->assertRedirect(route('login'));
    }

    public function test_customer_can_add_and_remove_product_from_wishlist(): void
    {
        $customer = $this->createCustomerUser();
        $product = Product::factory()->create();

        $this->actingAs($customer)->post(route('wishlist.toggle', $product))->assertRedirect();
        $this->assertDatabaseHas('wishlists', ['user_id' => $customer->id, 'product_id' => $product->id]);

        $this->actingAs($customer)->post(route('wishlist.toggle', $product))->assertRedirect();
        $this->assertDatabaseMissing('wishlists', ['user_id' => $customer->id, 'product_id' => $product->id]);
    }

    public function test_wishlist_index_lists_saved_products(): void
    {
        $customer = $this->createCustomerUser();
        $product = Product::factory()->create();
        $customer->wishlists()->create(['product_id' => $product->id]);

        $response = $this->actingAs($customer)->get(route('wishlist.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Customer/Wishlist')
            ->where('products.0.id', $product->id)
        );
    }
}
