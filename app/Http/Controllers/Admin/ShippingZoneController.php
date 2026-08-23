<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShippingZoneController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/ShippingZones/Index', [
            'zones' => ShippingZone::orderBy('cost')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'cost' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        ShippingZone::create($validated);

        return back()->with('success', 'Wilayah pengiriman berhasil ditambahkan.');
    }

    public function update(Request $request, ShippingZone $shippingZone): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'cost' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $shippingZone->update($validated);

        return back()->with('success', 'Wilayah pengiriman berhasil diperbarui.');
    }

    public function destroy(ShippingZone $shippingZone): RedirectResponse
    {
        $shippingZone->delete();

        return back()->with('success', 'Wilayah pengiriman berhasil dihapus.');
    }
}
