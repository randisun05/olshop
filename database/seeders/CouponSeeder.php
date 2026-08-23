<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        Coupon::create([
            'code' => 'HEMAT10',
            'type' => 'percent',
            'value' => 10,
            'min_purchase' => 50000,
        ]);

        Coupon::create([
            'code' => 'ONGKIR20K',
            'type' => 'fixed',
            'value' => 20000,
            'min_purchase' => 100000,
            'quota' => 50,
        ]);
    }
}
