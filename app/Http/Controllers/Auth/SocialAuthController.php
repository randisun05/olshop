<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SocialAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Provider yang didukung & sudah dikonfigurasi. Tambah di sini bila
     * provider baru ditambahkan (lihat config/services.php).
     */
    private const SUPPORTED_PROVIDERS = ['google'];

    public function __construct(private readonly SocialAuthService $socialAuthService) {}

    public function redirect(string $provider): RedirectResponse
    {
        $this->guardProvider($provider);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        $this->guardProvider($provider);

        try {
            $socialUser = Socialite::driver($provider)->user();
            $user = $this->socialAuthService->findOrCreateUser($provider, $socialUser);
        } catch (\Throwable $e) {
            return redirect()->route('login')->with('error', 'Login dengan '.ucfirst($provider).' gagal atau dibatalkan.');
        }

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard'));
    }

    private function guardProvider(string $provider): void
    {
        abort_unless(
            in_array($provider, self::SUPPORTED_PROVIDERS, true) && config("services.{$provider}.client_id"),
            404
        );
    }
}
