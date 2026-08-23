<?php

namespace Tests\Feature\Security;

use App\Models\ActivityLog;
use App\Models\Coupon;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_a_coupon_records_an_activity_log_entry(): void
    {
        $admin = $this->createAdminUser();
        $coupon = Coupon::factory()->create(['code' => 'AUDITME']);

        $this->actingAs($admin)->delete(route('admin.coupons.destroy', $coupon))->assertRedirect();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action' => 'coupon.delete',
            'subject_type' => $coupon->getMorphClass(),
            'subject_id' => $coupon->id,
        ]);
    }

    public function test_super_admin_can_view_activity_logs(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(PermissionSeeder::class);
        $superAdmin = User::factory()->create(['two_factor_confirmed_at' => now()]);
        $superAdmin->assignRole('Super Admin');
        ActivityLog::create(['action' => 'test.action', 'description' => 'Contoh log.', 'created_at' => now()]);

        $response = $this->actingAs($superAdmin)->get(route('admin.activity-logs.index'));

        $response->assertOk();
    }

    public function test_regular_admin_cannot_view_activity_logs(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->get(route('admin.activity-logs.index'));

        $response->assertForbidden();
    }
}
