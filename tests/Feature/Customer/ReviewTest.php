<?php

namespace Tests\Feature\Customer;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_review_a_completed_purchase(): void
    {
        $customer = $this->createCustomerUser();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $customer->id, 'status' => OrderStatus::Completed]);
        $orderItem = OrderItem::factory()->for($order)->create(['product_id' => $product->id]);

        $response = $this->actingAs($customer)->post(route('customer.reviews.store', $orderItem), [
            'rating' => 5,
            'comment' => 'Sangat bagus!',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'order_item_id' => $orderItem->id,
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'rating' => 5,
        ]);
    }

    public function test_customer_cannot_review_before_order_completed(): void
    {
        $customer = $this->createCustomerUser();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $customer->id, 'status' => OrderStatus::Shipped]);
        $orderItem = OrderItem::factory()->for($order)->create(['product_id' => $product->id]);

        $response = $this->actingAs($customer)->post(route('customer.reviews.store', $orderItem), [
            'rating' => 5,
        ]);

        $response->assertStatus(422);
    }

    public function test_customer_cannot_review_someone_elses_order_item(): void
    {
        $customer = $this->createCustomerUser();
        $order = Order::factory()->create(['status' => OrderStatus::Completed]);
        $orderItem = OrderItem::factory()->for($order)->create();

        $response = $this->actingAs($customer)->post(route('customer.reviews.store', $orderItem), [
            'rating' => 5,
        ]);

        $response->assertForbidden();
    }

    public function test_customer_cannot_review_the_same_item_twice(): void
    {
        $customer = $this->createCustomerUser();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $customer->id, 'status' => OrderStatus::Completed]);
        $orderItem = OrderItem::factory()->for($order)->create(['product_id' => $product->id]);
        $orderItem->review()->create(['product_id' => $product->id, 'user_id' => $customer->id, 'rating' => 4]);

        $response = $this->actingAs($customer)->post(route('customer.reviews.store', $orderItem), [
            'rating' => 5,
        ]);

        $response->assertStatus(422);
    }
}
