<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Fortify;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorSetupTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Rute two-factor.* Fortify dilindungi middleware `password.confirm`
     * (lihat config/fortify.php `confirmPassword => true`). Di test, kita
     * simulasikan sesi yang baru saja mengonfirmasi kata sandi dengan mengisi
     * kunci sesi yang sama seperti yang dicek Illuminate\Auth\Middleware\RequirePassword.
     */
    private function withConfirmedPassword(): static
    {
        return $this->withSession(['auth.password_confirmed_at' => time()]);
    }

    public function test_admin_can_enable_and_confirm_two_factor_authentication(): void
    {
        $admin = $this->createAdminUserWithoutTwoFactor();

        $this->actingAs($admin)->withConfirmedPassword()
            ->post(route('two-factor.enable'))
            ->assertRedirect();

        $admin->refresh();
        $this->assertNotNull($admin->two_factor_secret);
        $this->assertNull($admin->two_factor_confirmed_at);

        $secret = Fortify::currentEncrypter()->decrypt($admin->two_factor_secret);
        $validCode = (new Google2FA)->getCurrentOtp($secret);

        $this->actingAs($admin)->withConfirmedPassword()
            ->post(route('two-factor.confirm'), ['code' => $validCode])
            ->assertRedirect();

        $this->assertNotNull($admin->refresh()->two_factor_confirmed_at);
    }

    public function test_two_factor_confirmation_fails_with_invalid_code(): void
    {
        $admin = $this->createAdminUserWithoutTwoFactor();

        $this->actingAs($admin)->withConfirmedPassword()->post(route('two-factor.enable'));

        $response = $this->actingAs($admin)->withConfirmedPassword()
            ->post(route('two-factor.confirm'), ['code' => '000000']);

        $response->assertSessionHasErrors();
        $this->assertNull($admin->refresh()->two_factor_confirmed_at);
    }

    public function test_admin_can_disable_two_factor_authentication(): void
    {
        $admin = $this->createAdminUser();

        $this->actingAs($admin)->withConfirmedPassword()
            ->delete(route('two-factor.disable'))
            ->assertRedirect();

        $this->assertNull($admin->refresh()->two_factor_confirmed_at);
    }

    public function test_security_page_reflects_two_factor_status(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->get(route('account.security.edit'));

        $response->assertInertia(fn ($page) => $page
            ->component('Account/Security')
            ->where('twoFactorEnabled', true)
            ->where('twoFactorRequired', true)
        );
    }
}
