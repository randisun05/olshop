<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Verifikasi Google reCAPTCHA v3. Tidak melakukan apa-apa (selalu lolos)
 * ketika RECAPTCHA_SECRET_KEY belum diisi di .env — supaya dev/test tetap
 * jalan tanpa kredensial nyata dan tidak menambah dependensi eksternal wajib.
 * Isi RECAPTCHA_SITE_KEY & RECAPTCHA_SECRET_KEY di produksi untuk mengaktifkan.
 */
class Recaptcha implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $secretKey = config('services.recaptcha.secret_key');

        if (! $secretKey) {
            return;
        }

        if (! is_string($value) || $value === '') {
            $fail('Verifikasi reCAPTCHA gagal, silakan coba lagi.');

            return;
        }

        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secretKey,
                'response' => $value,
            ])->json();
        } catch (\Throwable $e) {
            Log::warning('reCAPTCHA verification request failed', ['error' => $e->getMessage()]);
            $fail('Verifikasi reCAPTCHA gagal, silakan coba lagi.');

            return;
        }

        if (! ($response['success'] ?? false) || ($response['score'] ?? 0) < 0.5) {
            $fail('Verifikasi reCAPTCHA gagal, silakan coba lagi.');
        }
    }
}
