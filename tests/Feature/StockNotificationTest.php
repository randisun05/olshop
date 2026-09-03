<?php

namespace Tests\Feature;

use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\ShippingZone;
use App\Notifications\LowStockAlert;
use App\Notifications\NewOrderPlaced;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockNotificationTest extends TestCase
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

    private function checkout(ShippingZone $zone): void
    {
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
            'terms_accepted' => true,
        ]);

        $response->assertRedirect();
    }

    public function test_admin_is_notified_when_a_new_order_is_placed(): void
    {
        $admin = $this->createAdminUser();
        $variant = ProductVariant::factory()->create(['stock' => 10, 'price' => 100000]);
        $zone = ShippingZone::factory()->create(['cost' => 15000, 'is_active' => true]);

        $this->addToCart($variant, 1);
        $this->checkout($zone);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $admin->id,
            'type' => NewOrderPlaced::class,
        ]);
    }

    public function test_staff_is_notified_when_stock_crosses_the_low_stock_threshold(): void
    {
        $admin = $this->createAdminUser();
        Setting::set('low_stock_threshold', '5');

        $variant = ProductVariant::factory()->create(['stock' => 6, 'price' => 100000]);
        $zone = ShippingZone::factory()->create(['cost' => 15000, 'is_active' => true]);

        $this->addToCart($variant, 2);
        $this->checkout($zone);

        $variant->refresh();
        $this->assertSame(4, $variant->stock);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $admin->id,
            'type' => LowStockAlert::class,
        ]);
    }

    public function test_no_low_stock_notification_when_stock_was_already_below_threshold(): void
    {
        $admin = $this->createAdminUser();
        Setting::set('low_stock_threshold', '5');

        $variant = ProductVariant::factory()->create(['stock' => 3, 'price' => 100000]);
        $zone = ShippingZone::factory()->create(['cost' => 15000, 'is_active' => true]);

        $this->addToCart($variant, 1);
        $this->checkout($zone);

        $this->assertDatabaseMissing('notifications', [
            'notifiable_id' => $admin->id,
            'type' => LowStockAlert::class,
        ]);
    }

    public function test_stock_adjustment_is_logged_when_order_is_placed(): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 10, 'price' => 100000]);
        $zone = ShippingZone::factory()->create(['cost' => 15000, 'is_active' => true]);

        $this->addToCart($variant, 3);
        $this->checkout($zone);

        $this->assertDatabaseHas('stock_adjustments', [
            'product_variant_id' => $variant->id,
            'type' => 'order_out',
            'quantity_change' => -3,
        ]);
    }
}
