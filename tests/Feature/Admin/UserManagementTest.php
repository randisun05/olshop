<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_a_staff_account(): void
    {
        $superAdmin = $this->createSuperAdminUser();

        $response = $this->actingAs($superAdmin)->post(route('admin.users.store'), [
            'name' => 'Staf Gudang Baru',
            'email' => 'staf.gudang@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'Staff Gudang',
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'staf.gudang@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->is_active);
        $this->assertTrue($user->hasRole('Staff Gudang'));
    }

    public function test_admin_without_super_admin_role_cannot_manage_users(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertForbidden();
    }

    public function test_super_admin_can_deactivate_a_staff_account(): void
    {
        $superAdmin = $this->createSuperAdminUser();
        $staff = User::factory()->create(['is_active' => true]);
        $staff->assignRole('Staff CS');

        $response = $this->actingAs($superAdmin)->post(route('admin.users.toggle-active', $staff));

        $response->assertRedirect();
        $this->assertFalse($staff->fresh()->is_active);
    }

    public function test_deactivated_staff_account_cannot_log_in(): void
    {
        $this->seed(RoleSeeder::class);

        $staff = User::factory()->create(['is_active' => false, 'two_factor_confirmed_at' => now()]);
        $staff->assignRole('Staff CS');

        $response = $this->post(route('login'), [
            'email' => $staff->email,
            'password' => 'password',
        ]);

        $response->assertForbidden();
        $this->assertGuest();
    }

    public function test_super_admin_cannot_deactivate_their_own_account(): void
    {
        $superAdmin = $this->createSuperAdminUser();

        $response = $this->actingAs($superAdmin)->post(route('admin.users.toggle-active', $superAdmin));

        $response->assertRedirect();
        $this->assertTrue($superAdmin->fresh()->is_active);
    }

    public function test_last_active_super_admin_cannot_be_deactivated_by_another_super_admin(): void
    {
        // $target adalah satu-satunya Super Admin yang masih aktif di sistem.
        // $actor punya role Super Admin (supaya lolos middleware role) tapi
        // statusnya sendiri nonaktif, sehingga tidak terhitung sebagai
        // "Super Admin aktif lain" oleh aturan proteksi di controller.
        $target = $this->createSuperAdminUser();
        $actor = User::factory()->create(['is_active' => false]);
        $actor->assignRole('Super Admin');

        $response = $this->actingAs($actor)->post(route('admin.users.toggle-active', $target));

        $response->assertRedirect();
        $this->assertTrue($target->fresh()->is_active, 'Super Admin aktif terakhir tidak boleh dinonaktifkan.');
    }
}
