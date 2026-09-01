<?php

namespace Tests\Feature\Auth;

use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class SocialAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.google.client_id', 'fake-client-id');
    }

    public function test_callback_logs_in_a_new_user_and_redirects_to_dashboard(): void
    {
        $this->seed(RoleSeeder::class);

        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-999',
            'email' => 'sosial@example.com',
            'name' => 'Pengguna Sosial',
        ]));

        $response = $this->get(route('social.callback', 'google'));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'sosial@example.com']);
    }

    public function test_unconfigured_provider_returns_404(): void
    {
        Config::set('services.google.client_id', null);

        $response = $this->get(route('social.redirect', 'google'));

        $response->assertNotFound();
    }

    public function test_unsupported_provider_returns_404(): void
    {
        $response = $this->get(route('social.redirect', 'facebook'));

        $response->assertNotFound();
    }

    public function test_authenticated_user_cannot_reach_social_redirect(): void
    {
        $customer = $this->createCustomerUser();

        $response = $this->actingAs($customer)->get(route('social.redirect', 'google'));

        $response->assertRedirect();
    }
}
