<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MandatoryTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_without_two_factor_is_redirected_to_security_page(): void
    {
        $admin = $this->createAdminUserWithoutTwoFactor();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertRedirect(route('account.security.edit'));
    }

    public function test_admin_with_two_factor_confirmed_can_access_admin_pages(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
    }

    public function test_staff_gudang_is_not_required_to_have_two_factor(): void
    {
        $staff = $this->createAdminUserWithoutTwoFactor();
        $staff->syncRoles(['Staff Gudang']);

        $response = $this->actingAs($staff)->get(route('admin.dashboard'));

        $response->assertOk();
    }

    public function test_security_page_itself_is_reachable_without_two_factor(): void
    {
        $admin = $this->createAdminUserWithoutTwoFactor();

        $response = $this->actingAs($admin)->get(route('account.security.edit'));

        $response->assertOk();
    }
}
