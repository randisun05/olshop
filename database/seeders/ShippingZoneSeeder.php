<?php

namespace Database\Seeders;

use App\Models\ShippingZone;
use Illuminate\Database\Seeder;

class ShippingZoneSeeder extends Seeder
{
    public function run(): void
    {
        ShippingZone::create(['name' => 'Dalam Kota', 'cost' => 10000]);
        ShippingZone::create(['name' => 'Luar Kota (Jawa)', 'cost' => 20000]);
        ShippingZone::create(['name' => 'Luar Pulau Jawa', 'cost' => 35000]);
    }
}
