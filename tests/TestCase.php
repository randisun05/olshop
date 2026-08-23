<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Cookie\CookieValuePrefix;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    protected function createAdminUser(): User
    {
        $this->seed(RoleSeeder::class);
        $this->seed(PermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('Admin');

        return $user;
    }

    protected function createCustomerUser(): User
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('Customer');

        return $user;
    }

    /**
     * Laravel's test HTTP client does not automatically carry cookies between
     * separate calls (unlike a real browser). Guest-session flows that span
     * more than one request (mis. tambah ke keranjang lalu checkout) need the
     * session id from the previous response replayed on the next request.
     *
     * Note: `withCookie()` always (re-)encrypts the value it's given (see
     * `prepareCookiesForRequest()`), so we must hand it the *plain* session
     * id here rather than the already-encrypted Set-Cookie value — passing
     * the encrypted value would get it encrypted a second time.
     */
    protected function carryGuestSession(TestResponse $response): static
    {
        $cookieName = config('session.cookie');

        foreach ($response->headers->getCookies() as $cookie) {
            if ($cookie->getName() === $cookieName) {
                $decrypted = decrypt($cookie->getValue(), false);
                $sessionId = CookieValuePrefix::remove($decrypted);

                $this->withCookie($cookieName, $sessionId);
                break;
            }
        }

        return $this;
    }
}
