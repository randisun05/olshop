<?php

namespace Tests\Feature\Admin;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_store_settings(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'store_name' => 'Toko Baru Jaya',
            'store_email' => 'kontak@tokobarujaya.test',
            'low_stock_threshold' => 10,
        ]);

        $response->assertRedirect();
        $this->assertSame('Toko Baru Jaya', Setting::get('store_name'));
        $this->assertSame('10', Setting::get('low_stock_threshold'));
    }

    public function test_customer_cannot_access_settings(): void
    {
        $customer = $this->createCustomerUser();

        $response = $this->actingAs($customer)->get(route('admin.settings.edit'));

        $response->assertForbidden();
    }
}
