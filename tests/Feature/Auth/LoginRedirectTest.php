<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresi: config('fortify.home') sempat diarahkan ke '/home', padahal
 * rute itu tidak pernah didaftarkan di routes/web.php (halaman utama ada
 * di '/') — akibatnya login tanpa "intended URL" tersimpan (mis. login
 * langsung dari /login, bukan setelah diarahkan dari halaman terproteksi)
 * berakhir di halaman 404. Baru terlihat lewat pengujian browser penuh
 * yang benar-benar mengikuti redirect, bukan cuma cek status code 302.
 */
class LoginRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_without_an_intended_url_redirects_to_a_real_page(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Customer');

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $this->followRedirects($response)->assertOk();
    }

    public function test_admin_login_without_an_intended_url_reaches_admin_dashboard(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $this->followRedirects($response)->assertOk();
    }
}
