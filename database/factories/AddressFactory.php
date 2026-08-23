<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'label' => 'Rumah',
            'recipient_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'city' => fake()->city(),
            'postal_code' => fake()->postcode(),
            'address_line' => fake()->address(),
            'is_default' => true,
        ];
    }
}
