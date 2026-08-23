<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CouponController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Coupons/Index', [
            'coupons' => Coupon::orderByDesc('id')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Coupons/Form');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        Coupon::create($validated);

        return redirect()->route('admin.coupons.index')->with('success', 'Kupon berhasil ditambahkan.');
    }

    public function edit(Coupon $coupon): Response
    {
        return Inertia::render('Admin/Coupons/Form', [
            'coupon' => $coupon,
        ]);
    }

    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        $validated = $this->validated($request, $coupon);

        $coupon->update($validated);

        return redirect()->route('admin.coupons.index')->with('success', 'Kupon berhasil diperbarui.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $coupon->delete();

        return back()->with('success', 'Kupon berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Coupon $coupon = null): array
    {
        return $request->validate([
            'code' => [
                'required', 'string', 'max:50', 'alpha_dash',
                Rule::unique('coupons', 'code')->ignore($coupon),
            ],
            'type' => ['required', Rule::in(['percent', 'fixed'])],
            'value' => [
                'required', 'numeric', 'min:0',
                Rule::when($request->input('type') === 'percent', ['max:100']),
            ],
            'min_purchase' => ['nullable', 'numeric', 'min:0'],
            'quota' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ]);
    }
}
