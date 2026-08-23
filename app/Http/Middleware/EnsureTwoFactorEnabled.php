<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorEnabled
{
    /**
     * 2FA wajib untuk Super Admin & Admin (docs/PERENCANAAN.md § 7). Staff
     * Gudang/CS tidak diwajibkan agar operasional harian tidak terhambat.
     */
    private const REQUIRED_ROLES = ['Super Admin', 'Admin'];

    /**
     * Rute yang harus tetap bisa diakses walau 2FA belum aktif, agar user
     * bisa menyelesaikan proses aktivasi tanpa terjebak redirect loop.
     */
    private const ALLOWED_ROUTES = [
        'account.security.edit',
        'password.confirm',
        'two-factor.enable',
        'two-factor.confirm',
        'two-factor.qr-code',
        'two-factor.secret-key',
        'two-factor.recovery-codes',
        'two-factor.regenerate-recovery-codes',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user
            && $user->hasAnyRole(self::REQUIRED_ROLES)
            && is_null($user->two_factor_confirmed_at)
            && ! in_array($request->route()?->getName(), self::ALLOWED_ROUTES, true)
        ) {
            return redirect()->route('account.security.edit')
                ->with('warning', 'Aktifkan autentikasi dua faktor (2FA) terlebih dahulu untuk melanjutkan sebagai admin.');
        }

        return $next($request);
    }
}
