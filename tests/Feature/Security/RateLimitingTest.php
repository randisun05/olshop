<?php

namespace Tests\Feature\Security;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    public function test_wishlist_toggle_is_rate_limited(): void
    {
        $customer = $this->createCustomerUser();
        $product = Product::factory()->create();

        $this->actingAs($customer);

        for ($i = 0; $i < 30; $i++) {
            $this->post(route('wishlist.toggle', $product))->assertStatus(302);
        }

        $this->post(route('wishlist.toggle', $product))->assertStatus(429);
    }

    public function test_coupon_check_endpoint_is_rate_limited(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $this->post(route('checkout.coupon.check'), ['code' => 'TIDAKADA']);
        }

        $this->post(route('checkout.coupon.check'), ['code' => 'TIDAKADA'])->assertStatus(429);
    }
}
