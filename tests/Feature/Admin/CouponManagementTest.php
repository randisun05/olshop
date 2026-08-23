<?php

namespace Tests\Feature\Admin;

use App\Models\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_coupon(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->post(route('admin.coupons.store'), [
            'code' => 'DISKON20',
            'type' => 'percent',
            'value' => 20,
            'min_purchase' => 50000,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.coupons.index'));
        $this->assertDatabaseHas('coupons', ['code' => 'DISKON20', 'type' => 'percent']);
    }

    public function test_percent_coupon_value_cannot_exceed_100(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->post(route('admin.coupons.store'), [
            'code' => 'INVALID',
            'type' => 'percent',
            'value' => 150,
        ]);

        $response->assertSessionHasErrors('value');
    }

    public function test_customer_cannot_manage_coupons(): void
    {
        $customer = $this->createCustomerUser();

        $response = $this->actingAs($customer)->get(route('admin.coupons.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_delete_coupon(): void
    {
        $admin = $this->createAdminUser();
        $coupon = Coupon::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.coupons.destroy', $coupon));

        $response->assertRedirect();
        $this->assertDatabaseMissing('coupons', ['id' => $coupon->id]);
    }
}
