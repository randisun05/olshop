<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
        ]);

        $admin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@tokoonline.test',
        ]);
        $admin->assignRole('Super Admin');

        $customer = User::factory()->create([
            'name' => 'Pelanggan Contoh',
            'email' => 'customer@tokoonline.test',
        ]);
        $customer->assignRole('Customer');

        $this->call([
            CatalogSeeder::class,
            ShippingZoneSeeder::class,
            CouponSeeder::class,
            SettingSeeder::class,
            FaqSeeder::class,
        ]);
    }
}
