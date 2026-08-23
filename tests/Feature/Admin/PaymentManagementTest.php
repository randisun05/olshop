<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\Payment;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_verify_manual_payment(): void
    {
        $admin = $this->createAdminUser();
        $order = Order::factory()->create();
        $payment = Payment::factory()->for($order)->create(['method' => 'manual_transfer', 'status' => 'pending']);

        $response = $this->actingAs($admin)->post(route('admin.payments.verify', $payment));

        $response->assertRedirect();
        $this->assertSame('paid', $payment->fresh()->status->value);
        $this->assertSame('paid', $order->fresh()->status->value);
    }

    public function test_admin_can_reject_manual_payment_and_stock_is_restored(): void
    {
        $admin = $this->createAdminUser();
        $variant = ProductVariant::factory()->create(['stock' => 5]);
        $order = Order::factory()->create();
        $order->items()->create([
            'product_id' => $variant->product_id,
            'product_variant_id' => $variant->id,
            'product_name' => 'Test',
            'variant_label' => 'Standar',
            'price' => 10000,
            'quantity' => 3,
            'subtotal' => 30000,
        ]);
        $payment = Payment::factory()->for($order)->create(['method' => 'manual_transfer', 'status' => 'pending']);

        $response = $this->actingAs($admin)->post(route('admin.payments.reject', $payment));

        $response->assertRedirect();
        $this->assertSame('failed', $payment->fresh()->status->value);
        $this->assertSame(8, $variant->fresh()->stock);
    }
}
