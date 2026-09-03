<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RejectInactiveUserLogin
{
    public function handle(Login $event): void
    {
        // Refresh dari DB, bukan pakai atribut di memori: model User yang
        // baru saja dibuat lewat create() (mis. alur registrasi/login sosial)
        // tidak otomatis membawa nilai default kolom `is_active` dari
        // database ke instance PHP-nya.
        if (! $event->user->fresh()?->is_active) {
            Auth::guard($event->guard)->logout();

            throw new HttpException(403, 'Akun Anda telah dinonaktifkan. Hubungi administrator toko.');
        }
    }
}
