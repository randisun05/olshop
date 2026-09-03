<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LoginRecaptchaTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_works_normally_when_recaptcha_is_not_configured(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_when_recaptcha_verification_fails(): void
    {
        Config::set('services.recaptcha.secret_key', 'fake-secret');
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => false]),
        ]);

        $user = User::factory()->create();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
            'recaptcha_token' => 'a-token',
        ]);

        $response->assertSessionHasErrors('recaptcha_token');
        $this->assertGuest();
    }

    public function test_login_succeeds_when_recaptcha_verification_passes(): void
    {
        Config::set('services.recaptcha.secret_key', 'fake-secret');
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true, 'score' => 0.9]),
        ]);

        $user = User::factory()->create();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
            'recaptcha_token' => 'a-token',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }
}
