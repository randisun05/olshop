<?php

namespace Tests\Feature\Customer;

use App\Enums\ComplaintStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComplaintTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_file_a_complaint_for_a_completed_order(): void
    {
        $customer = $this->createCustomerUser();
        $order = Order::factory()->create(['user_id' => $customer->id, 'status' => OrderStatus::Completed]);
        $item = OrderItem::factory()->for($order)->create();

        $response = $this->actingAs($customer)->post(route('customer.complaints.store', $order->order_number), [
            'order_item_id' => $item->id,
            'type' => 'retur',
            'reason' => 'Barang yang diterima rusak.',
        ]);

        $response->assertRedirect(route('customer.complaints.index'));
        $this->assertDatabaseHas('complaints', [
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'user_id' => $customer->id,
            'type' => 'retur',
            'status' => 'pending',
        ]);
    }

    public function test_customer_cannot_file_complaint_for_order_that_is_not_completed(): void
    {
        $customer = $this->createCustomerUser();
        $order = Order::factory()->create(['user_id' => $customer->id, 'status' => OrderStatus::Shipped]);

        $response = $this->actingAs($customer)->post(route('customer.complaints.store', $order->order_number), [
            'type' => 'komplain',
            'reason' => 'Belum diterima.',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('complaints', 0);
    }

    public function test_customer_cannot_file_complaint_for_someone_elses_order(): void
    {
        $customer = $this->createCustomerUser();
        $order = Order::factory()->create(['status' => OrderStatus::Completed]);

        $response = $this->actingAs($customer)->post(route('customer.complaints.store', $order->order_number), [
            'type' => 'komplain',
            'reason' => 'Bukan pesanan saya.',
        ]);

        $response->assertForbidden();
    }

    public function test_customer_cannot_file_a_second_open_complaint_for_the_same_order(): void
    {
        $customer = $this->createCustomerUser();
        $order = Order::factory()->create(['user_id' => $customer->id, 'status' => OrderStatus::Completed]);
        $order->complaints()->create([
            'user_id' => $customer->id,
            'type' => 'komplain',
            'reason' => 'Pengajuan pertama.',
            'status' => ComplaintStatus::Pending,
        ]);

        $response = $this->actingAs($customer)->post(route('customer.complaints.store', $order->order_number), [
            'type' => 'komplain',
            'reason' => 'Pengajuan kedua.',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('complaints', 1);
    }

    public function test_customer_can_file_new_complaint_after_previous_one_is_resolved(): void
    {
        $customer = $this->createCustomerUser();
        $order = Order::factory()->create(['user_id' => $customer->id, 'status' => OrderStatus::Completed]);
        $order->complaints()->create([
            'user_id' => $customer->id,
            'type' => 'komplain',
            'reason' => 'Pengajuan pertama.',
            'status' => ComplaintStatus::Resolved,
        ]);

        $response = $this->actingAs($customer)->post(route('customer.complaints.store', $order->order_number), [
            'type' => 'komplain',
            'reason' => 'Pengajuan baru, masalah lain.',
        ]);

        $response->assertRedirect(route('customer.complaints.index'));
        $this->assertDatabaseCount('complaints', 2);
    }
}
