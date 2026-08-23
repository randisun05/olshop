<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::set('store_name', 'Toko Online');
        Setting::set('store_email', 'halo@tokoonline.test');
        Setting::set('store_phone', '021-1234567');
        Setting::set('low_stock_threshold', '5');
    }
}
