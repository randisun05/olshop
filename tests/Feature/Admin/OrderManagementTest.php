<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Notifications\OrderStatusUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_progress_order_from_paid_to_completed(): void
    {
        Notification::fake();

        $admin = $this->createAdminUser();
        $order = Order::factory()->create(['status' => OrderStatus::Paid]);

        $this->actingAs($admin)->post(route('admin.orders.process', $order))->assertRedirect();
        $this->assertSame('processing', $order->fresh()->status->value);

        $this->actingAs($admin)->post(route('admin.orders.ship', $order), [
            'courier' => 'JNE',
            'tracking_number' => 'JNE123456789',
        ])->assertRedirect();
        $order->refresh();
        $this->assertSame('shipped', $order->status->value);
        $this->assertDatabaseHas('shipments', [
            'order_id' => $order->id,
            'courier' => 'JNE',
            'tracking_number' => 'JNE123456789',
        ]);

        $this->actingAs($admin)->post(route('admin.orders.complete', $order))->assertRedirect();
        $this->assertSame('completed', $order->fresh()->status->value);

        Notification::assertSentOnDemand(OrderStatusUpdated::class, 3);
    }

    public function test_order_cannot_be_shipped_before_processing(): void
    {
        $admin = $this->createAdminUser();
        $order = Order::factory()->create(['status' => OrderStatus::Paid]);

        $response = $this->actingAs($admin)->post(route('admin.orders.ship', $order), [
            'courier' => 'JNE',
            'tracking_number' => 'JNE123456789',
        ]);

        $response->assertStatus(422);
        $this->assertSame('paid', $order->fresh()->status->value);
    }

    public function test_admin_can_cancel_order_and_stock_is_restored(): void
    {
        $admin = $this->createAdminUser();
        $order = Order::factory()->create(['status' => OrderStatus::Paid]);
        $variant = ProductVariant::factory()->create(['stock' => 5]);
        $order->items()->create([
            'product_id' => $variant->product_id,
            'product_variant_id' => $variant->id,
            'product_name' => 'Test',
            'variant_label' => 'Standar',
            'price' => 10000,
            'quantity' => 2,
            'subtotal' => 20000,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.orders.cancel', $order));

        $response->assertRedirect();
        $this->assertSame('cancelled', $order->fresh()->status->value);
        $this->assertSame(7, $variant->fresh()->stock);
    }

    public function test_shipped_order_cannot_be_cancelled(): void
    {
        $admin = $this->createAdminUser();
        $order = Order::factory()->create(['status' => OrderStatus::Shipped]);

        $response = $this->actingAs($admin)->post(route('admin.orders.cancel', $order));

        $response->assertStatus(422);
    }
}
