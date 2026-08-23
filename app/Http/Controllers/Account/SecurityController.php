<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class SecurityController extends Controller
{
    public function edit(): Response
    {
        $user = request()->user();

        return Inertia::render('Account/Security', [
            'twoFactorEnabled' => ! is_null($user->two_factor_confirmed_at),
            'twoFactorRequired' => $user->hasAnyRole(['Super Admin', 'Admin']),
        ]);
    }
}
