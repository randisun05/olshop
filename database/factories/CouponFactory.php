<?php

namespace Database\Factories;

use App\Enums\CouponType;
use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => Str::upper(Str::random(8)),
            'type' => CouponType::Fixed,
            'value' => 10000,
            'min_purchase' => 0,
            'quota' => null,
            'used_count' => 0,
            'expires_at' => null,
            'is_active' => true,
        ];
    }
}
