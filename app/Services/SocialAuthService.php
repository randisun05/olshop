<?php

namespace App\Services;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class SocialAuthService
{
    /**
     * Cari user yang sudah pernah login dengan akun sosial ini; jika belum,
     * tautkan ke user existing lewat email (aman karena provider OAuth sudah
     * memverifikasi kepemilikan email), atau buat user baru.
     *
     * @throws ValidationException
     */
    public function findOrCreateUser(string $provider, SocialiteUser $socialUser): User
    {
        $existingLink = SocialAccount::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($existingLink) {
            return $existingLink->user;
        }

        $email = $socialUser->getEmail();

        if (! $email) {
            throw ValidationException::withMessages([
                'email' => "Akun {$provider} Anda tidak memiliki email publik. Gunakan metode login lain.",
            ]);
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            $user = User::create([
                'name' => $socialUser->getName() ?: $socialUser->getNickname() ?: Str::before($email, '@'),
                'email' => $email,
                'password' => Hash::make(Str::random(40)),
            ]);
            // email_verified_at bukan mass-assignable (lihat #[Fillable] di User),
            // di-set terpisah karena OAuth provider sudah memverifikasi email ini.
            $user->forceFill(['email_verified_at' => now()])->save();
            $user->assignRole('Customer');
        }

        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
        ]);

        return $user;
    }
}
