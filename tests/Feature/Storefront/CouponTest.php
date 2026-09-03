<?php

namespace Tests\Feature\Storefront;

use App\Models\Coupon;
use App\Models\ProductVariant;
use App\Models\ShippingZone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponTest extends TestCase
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

    private function checkoutPayload(array $overrides = []): array
    {
        return array_merge([
            'recipient_name' => 'Budi',
            'phone' => '08123456789',
            'city' => 'Jakarta',
            'address_line' => 'Jl. Contoh No. 1',
            'guest_name' => 'Budi',
            'guest_email' => 'budi@example.com',
            'guest_phone' => '08123456789',
            'payment_method' => 'manual_transfer',
            'terms_accepted' => true,
        ], $overrides);
    }

    public function test_valid_coupon_reduces_order_total(): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 10, 'price' => 100000]);
        $zone = ShippingZone::factory()->create(['cost' => 10000, 'is_active' => true]);
        $coupon = Coupon::factory()->create(['code' => 'HEMAT10', 'type' => 'fixed', 'value' => 20000, 'min_purchase' => 0]);

        $this->addToCart($variant);

        $response = $this->post(route('checkout.store'), $this->checkoutPayload([
            'shipping_zone_id' => $zone->id,
            'coupon_code' => 'HEMAT10',
        ]));

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'subtotal' => 100000,
            'discount' => 20000,
            'shipping_cost' => 10000,
            'total' => 90000,
        ]);
        $this->assertDatabaseHas('coupon_usages', ['coupon_id' => $coupon->id]);
        $this->assertSame(1, $coupon->fresh()->used_count);
    }

    public function test_checkout_fails_with_invalid_coupon_code(): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 10, 'price' => 100000]);
        $zone = ShippingZone::factory()->create(['is_active' => true]);

        $this->addToCart($variant);

        $response = $this->post(route('checkout.store'), $this->checkoutPayload([
            'shipping_zone_id' => $zone->id,
            'coupon_code' => 'TIDAKADA',
        ]));

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_coupon_cannot_be_used_beyond_quota(): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 10, 'price' => 100000]);
        $zone = ShippingZone::factory()->create(['is_active' => true]);
        Coupon::factory()->create(['code' => 'LIMIT1', 'quota' => 1, 'used_count' => 1]);

        $this->addToCart($variant);

        $response = $this->post(route('checkout.store'), $this->checkoutPayload([
            'shipping_zone_id' => $zone->id,
            'coupon_code' => 'LIMIT1',
        ]));

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('orders', 0);
    }
}
