<?php

namespace Tests\Feature\Storefront;

use App\Models\Address;
use App\Models\ProductVariant;
use App\Models\ShippingZone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function addToCart(ProductVariant $variant, int $qty = 1): void
    {
        $response = $this->post(route('cart.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => $qty,
        ]);

        $this->carryGuestSession($response);
    }

    public function test_guest_can_checkout_with_manual_transfer_and_stock_is_reduced(): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 10, 'price' => 100000]);
        $zone = ShippingZone::factory()->create(['cost' => 15000, 'is_active' => true]);

        $this->addToCart($variant, 2);

        $response = $this->post(route('checkout.store'), [
            'recipient_name' => 'Budi',
            'phone' => '08123456789',
            'city' => 'Jakarta',
            'address_line' => 'Jl. Contoh No. 1',
            'guest_name' => 'Budi',
            'guest_email' => 'budi@example.com',
            'guest_phone' => '08123456789',
            'shipping_zone_id' => $zone->id,
            'payment_method' => 'manual_transfer',
        ]);

        $response->assertRedirect();

        $variant->refresh();
        $this->assertSame(8, $variant->stock);

        $this->assertDatabaseHas('orders', [
            'guest_email' => 'budi@example.com',
            'subtotal' => 200000,
            'shipping_cost' => 15000,
            'total' => 215000,
        ]);
        $this->assertDatabaseHas('payments', ['method' => 'manual_transfer', 'status' => 'pending']);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_checkout_fails_when_stock_is_insufficient(): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 2]);
        $zone = ShippingZone::factory()->create(['is_active' => true]);

        $this->addToCart($variant, 2);

        // Stok berkurang (mis. dibeli orang lain) setelah item ada di keranjang.
        $variant->update(['stock' => 1]);

        $response = $this->post(route('checkout.store'), [
            'recipient_name' => 'Budi',
            'phone' => '08123456789',
            'city' => 'Jakarta',
            'address_line' => 'Jl. Contoh No. 1',
            'guest_name' => 'Budi',
            'guest_email' => 'budi@example.com',
            'guest_phone' => '08123456789',
            'shipping_zone_id' => $zone->id,
            'payment_method' => 'manual_transfer',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(1, $variant->fresh()->stock);
    }

    public function test_logged_in_customer_can_checkout_with_saved_address(): void
    {
        $customer = $this->createCustomerUser();
        $address = Address::factory()->for($customer)->create();
        $variant = ProductVariant::factory()->create(['stock' => 5]);
        $zone = ShippingZone::factory()->create(['is_active' => true]);

        $this->actingAs($customer)->post(route('cart.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($customer)->post(route('checkout.store'), [
            'use_saved_address' => true,
            'address_id' => $address->id,
            'shipping_zone_id' => $zone->id,
            'payment_method' => 'manual_transfer',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'user_id' => $customer->id,
            'recipient_name' => $address->recipient_name,
        ]);
    }
}
