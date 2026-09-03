<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SecurityController extends Controller
{
    /**
     * Role staf tidak boleh menghapus akunnya sendiri lewat sini — akun
     * tersebut punya jejak audit (log aktivitas, riwayat status pesanan
     * yang diubah, dst) yang harus tetap utuh. Penonaktifan akun staf
     * sudah ditangani terpisah oleh Super Admin di /admin/users.
     */
    private const STAFF_ROLES = ['Super Admin', 'Admin', 'Staff Gudang', 'Staff CS'];

    public function edit(): Response
    {
        $user = request()->user();

        return Inertia::render('Account/Security', [
            'twoFactorEnabled' => ! is_null($user->two_factor_confirmed_at),
            'twoFactorRequired' => $user->hasAnyRole(['Super Admin', 'Admin']),
            'canDeleteOwnAccount' => ! $user->hasAnyRole(self::STAFF_ROLES),
        ]);
    }

    public function destroyAccount(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_if(
            $user->hasAnyRole(self::STAFF_ROLES),
            403,
            'Akun staf tidak bisa dihapus sendiri. Hubungi Super Admin untuk menonaktifkan akun Anda.'
        );

        $request->validate(['password' => ['required', 'current_password']]);

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Akun Anda berhasil dihapus. Terima kasih pernah berbelanja di sini.');
    }
}
