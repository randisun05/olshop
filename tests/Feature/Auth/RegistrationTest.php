<?php

namespace Tests\Feature\Auth;

use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_registration_requires_accepting_terms(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('terms_accepted');
        $this->assertDatabaseMissing('users', ['email' => 'budi@example.com']);
    }

    public function test_registration_succeeds_when_terms_are_accepted(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms_accepted' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'budi@example.com']);
    }
}
