<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AddressController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Customer/Addresses', [
            'addresses' => $request->user()->addresses()->orderByDesc('is_default')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        DB::transaction(function () use ($request, $validated) {
            if ($validated['is_default'] ?? false) {
                $request->user()->addresses()->update(['is_default' => false]);
            }

            $request->user()->addresses()->create($validated);
        });

        return back()->with('success', 'Alamat berhasil ditambahkan.');
    }

    public function update(Request $request, Address $address): RedirectResponse
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        $validated = $this->validated($request);

        DB::transaction(function () use ($request, $address, $validated) {
            if ($validated['is_default'] ?? false) {
                $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            }

            $address->update($validated);
        });

        return back()->with('success', 'Alamat berhasil diperbarui.');
    }

    public function destroy(Request $request, Address $address): RedirectResponse
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        $address->delete();

        return back()->with('success', 'Alamat berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'city' => ['required', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'address_line' => ['required', 'string'],
            'is_default' => ['boolean'],
        ]);
    }
}
