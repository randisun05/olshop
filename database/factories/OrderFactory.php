<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(50000, 500000);
        $shippingCost = fake()->numberBetween(10000, 30000);

        return [
            'order_number' => 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
            'guest_name' => fake()->name(),
            'guest_email' => fake()->unique()->safeEmail(),
            'guest_phone' => fake()->phoneNumber(),
            'status' => OrderStatus::PendingPayment,
            'recipient_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'city' => fake()->city(),
            'address_line' => fake()->address(),
            'shipping_zone_name' => 'Dalam Kota',
            'shipping_cost' => $shippingCost,
            'subtotal' => $subtotal,
            'total' => $subtotal + $shippingCost,
        ];
    }
}
