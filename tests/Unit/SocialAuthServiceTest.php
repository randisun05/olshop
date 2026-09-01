<?php

namespace Tests\Unit;

use App\Models\SocialAccount;
use App\Models\User;
use App\Services\SocialAuthService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class SocialAuthServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_a_new_user_when_no_account_matches(): void
    {
        $this->seed(RoleSeeder::class);
        $service = new SocialAuthService;

        $socialUser = SocialiteUser::fake(['id' => 'google-123', 'email' => 'baru@example.com', 'name' => 'Pengguna Baru']);

        $user = $service->findOrCreateUser('google', $socialUser);

        $this->assertDatabaseHas('users', ['email' => 'baru@example.com']);
        $this->assertTrue($user->hasRole('Customer'));
        $this->assertNotNull($user->email_verified_at);
        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-123',
        ]);
    }

    public function test_links_social_account_to_existing_user_matched_by_email(): void
    {
        $this->seed(RoleSeeder::class);
        $existing = User::factory()->create(['email' => 'sudahdaftar@example.com']);
        $existing->assignRole('Customer');
        $service = new SocialAuthService;

        $socialUser = SocialiteUser::fake(['id' => 'google-456', 'email' => 'sudahdaftar@example.com']);

        $user = $service->findOrCreateUser('google', $socialUser);

        $this->assertSame($existing->id, $user->id);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $existing->id,
            'provider' => 'google',
            'provider_id' => 'google-456',
        ]);
    }

    public function test_returns_existing_user_on_repeat_login_without_duplicating_link(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create(['email' => 'berulang@example.com']);
        $user->assignRole('Customer');
        SocialAccount::create(['user_id' => $user->id, 'provider' => 'google', 'provider_id' => 'google-789']);
        $service = new SocialAuthService;

        $socialUser = SocialiteUser::fake(['id' => 'google-789', 'email' => 'berulang@example.com']);
        $result = $service->findOrCreateUser('google', $socialUser);

        $this->assertSame($user->id, $result->id);
        $this->assertDatabaseCount('social_accounts', 1);
    }

    public function test_throws_when_social_account_has_no_email(): void
    {
        $service = new SocialAuthService;
        $socialUser = SocialiteUser::fake(['id' => 'google-no-email', 'email' => null]);

        $this->expectException(ValidationException::class);

        $service->findOrCreateUser('google', $socialUser);
    }
}
