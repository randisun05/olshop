<?php

namespace App\Actions\Fortify;

use App\Rules\Recaptcha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Pipe tambahan di alur login Fortify (lihat FortifyServiceProvider). Sama
 * seperti CreateNewUser (registrasi), tidak melakukan apa-apa selama
 * RECAPTCHA_SECRET_KEY belum diisi — lihat App\Rules\Recaptcha.
 */
class EnsureRecaptchaPassed
{
    public function handle(Request $request, callable $next): mixed
    {
        Validator::make($request->all(), [
            'recaptcha_token' => ['nullable', 'string', new Recaptcha],
        ])->validate();

        return $next($request);
    }
}
