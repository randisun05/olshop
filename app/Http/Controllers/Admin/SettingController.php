<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    /**
     * Kunci pengaturan yang didukung. Ditambah di sini saat modul baru butuh
     * pengaturan baru, lihat docs/PERENCANAAN.md § settings.
     */
    private const KEYS = [
        'store_name',
        'store_email',
        'store_phone',
        'store_address',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'tax_percent',
        'low_stock_threshold',
    ];

    public function edit(): Response
    {
        $settings = Setting::allCached()->only(self::KEYS);
        $logoPath = Setting::get('store_logo');

        return Inertia::render('Admin/Settings/Edit', [
            'settings' => $settings,
            'logoUrl' => $logoPath ? Storage::disk('public')->url($logoPath) : null,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'store_email' => ['nullable', 'email', 'max:255'],
            'store_phone' => ['nullable', 'string', 'max:50'],
            'store_address' => ['nullable', 'string', 'max:1000'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:100'],
            'bank_account_holder' => ['nullable', 'string', 'max:255'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'logo' => ['nullable', 'image', 'max:1024'],
        ]);

        foreach (self::KEYS as $key) {
            $value = $validated[$key] ?? null;
            Setting::set($key, $value === null ? null : (string) $value);
        }

        if ($request->hasFile('logo')) {
            $oldLogo = Setting::get('store_logo');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }
            Setting::set('store_logo', $request->file('logo')->store('settings', 'public'));
        }

        ActivityLog::record('settings.update', 'Memperbarui pengaturan toko.');

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
