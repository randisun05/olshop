<?php

namespace Tests\Feature\Customer;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_confirm_receipt_of_shipped_order(): void
    {
        $customer = $this->createCustomerUser();
        $order = Order::factory()->create(['user_id' => $customer->id, 'status' => OrderStatus::Shipped]);

        $response = $this->actingAs($customer)->post(route('customer.orders.confirm', $order));

        $response->assertRedirect();
        $this->assertSame('completed', $order->fresh()->status->value);
    }

    public function test_customer_cannot_confirm_receipt_of_someone_elses_order(): void
    {
        $customer = $this->createCustomerUser();
        $otherOrder = Order::factory()->create(['status' => OrderStatus::Shipped]);

        $response = $this->actingAs($customer)->post(route('customer.orders.confirm', $otherOrder));

        $response->assertForbidden();
    }

    public function test_customer_cannot_confirm_receipt_before_order_is_shipped(): void
    {
        $customer = $this->createCustomerUser();
        $order = Order::factory()->create(['user_id' => $customer->id, 'status' => OrderStatus::Processing]);

        $response = $this->actingAs($customer)->post(route('customer.orders.confirm', $order));

        $response->assertStatus(422);
    }
}
