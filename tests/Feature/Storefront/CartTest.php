<?php

namespace Tests\Feature\Storefront;

use App\Models\Cart;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_add_item_to_cart(): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 10, 'price' => 50000]);

        $response = $this->post(route('cart.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('cart_items', [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);
    }

    public function test_cart_quantity_cannot_exceed_stock(): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 3]);

        $this->post(route('cart.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 10,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'product_variant_id' => $variant->id,
            'quantity' => 3,
        ]);
    }

    public function test_visitor_cannot_modify_someone_elses_cart_item(): void
    {
        $variant = ProductVariant::factory()->create();
        $otherCart = Cart::create(['session_id' => 'someone-elses-session']);
        $item = $otherCart->items()->create(['product_variant_id' => $variant->id, 'quantity' => 1]);

        $response = $this->put(route('cart.update', $item->id), ['quantity' => 5]);

        $response->assertForbidden();
    }
}
