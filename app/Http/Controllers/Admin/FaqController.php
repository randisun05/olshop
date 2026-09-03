<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFaqEntryRequest;
use App\Http\Requests\Admin\UpdateFaqEntryRequest;
use App\Models\FaqEntry;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class FaqController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Faq/Index', [
            'entries' => FaqEntry::orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Faq/Form');
    }

    public function store(StoreFaqEntryRequest $request): RedirectResponse
    {
        FaqEntry::create([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.faq.index')->with('success', 'Entri FAQ berhasil ditambahkan.');
    }

    public function edit(FaqEntry $faq): Response
    {
        return Inertia::render('Admin/Faq/Form', [
            'entry' => $faq,
        ]);
    }

    public function update(UpdateFaqEntryRequest $request, FaqEntry $faq): RedirectResponse
    {
        $faq->update([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.faq.index')->with('success', 'Entri FAQ berhasil diperbarui.');
    }

    public function destroy(FaqEntry $faq): RedirectResponse
    {
        $faq->delete();

        return back()->with('success', 'Entri FAQ berhasil dihapus.');
    }
}
