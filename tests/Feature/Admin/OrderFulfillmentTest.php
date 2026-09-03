<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OrderFulfillmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_gudang_can_view_order_list_and_detail(): void
    {
        $staff = $this->createStaffGudangUser();
        $order = Order::factory()->create(['status' => OrderStatus::Paid]);

        $this->actingAs($staff)->get(route('admin.orders.index'))->assertOk();
        $this->actingAs($staff)->get(route('admin.orders.show', $order))->assertOk();
    }

    public function test_staff_gudang_can_mark_order_processing_and_shipped(): void
    {
        Notification::fake();

        $staff = $this->createStaffGudangUser();
        $order = Order::factory()->create(['status' => OrderStatus::Paid]);

        $this->actingAs($staff)->post(route('admin.orders.process', $order))->assertRedirect();
        $this->assertSame('processing', $order->fresh()->status->value);

        $this->actingAs($staff)->post(route('admin.orders.ship', $order), [
            'courier' => 'JNE',
            'tracking_number' => 'JNE123456789',
        ])->assertRedirect();
        $this->assertSame('shipped', $order->fresh()->status->value);
    }

    public function test_staff_gudang_can_download_packing_slip(): void
    {
        $staff = $this->createStaffGudangUser();
        $order = Order::factory()->create(['status' => OrderStatus::Processing]);

        $response = $this->actingAs($staff)->get(route('admin.orders.packing-slip', $order));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_staff_gudang_cannot_mark_order_completed(): void
    {
        $staff = $this->createStaffGudangUser();
        $order = Order::factory()->create(['status' => OrderStatus::Shipped]);

        $response = $this->actingAs($staff)->post(route('admin.orders.complete', $order));

        $response->assertForbidden();
        $this->assertSame('shipped', $order->fresh()->status->value);
    }

    public function test_staff_gudang_cannot_cancel_order(): void
    {
        $staff = $this->createStaffGudangUser();
        $order = Order::factory()->create(['status' => OrderStatus::Paid]);

        $response = $this->actingAs($staff)->post(route('admin.orders.cancel', $order));

        $response->assertForbidden();
        $this->assertSame('paid', $order->fresh()->status->value);
    }

    public function test_staff_gudang_cannot_access_payment_verification(): void
    {
        $staff = $this->createStaffGudangUser();

        $response = $this->actingAs($staff)->get(route('admin.payments.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_still_complete_and_cancel_orders(): void
    {
        Notification::fake();

        $admin = $this->createAdminUser();
        $order = Order::factory()->create(['status' => OrderStatus::Shipped]);

        $this->actingAs($admin)->post(route('admin.orders.complete', $order))->assertRedirect();
        $this->assertSame('completed', $order->fresh()->status->value);
    }
}
