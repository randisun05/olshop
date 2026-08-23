<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAttributeRequest;
use App\Http\Requests\Admin\UpdateAttributeRequest;
use App\Models\Attribute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AttributeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Attributes/Index', [
            'attributes' => Attribute::with('values')->orderBy('name')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Attributes/Form');
    }

    public function store(StoreAttributeRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $attribute = Attribute::create(['name' => $request->validated('name')]);

            foreach ($request->validated('values') as $value) {
                $attribute->values()->create(['value' => $value]);
            }
        });

        return redirect()->route('admin.attributes.index')->with('success', 'Atribut berhasil ditambahkan.');
    }

    public function edit(Attribute $attribute): Response
    {
        return Inertia::render('Admin/Attributes/Form', [
            'attribute' => $attribute->load('values'),
        ]);
    }

    public function update(UpdateAttributeRequest $request, Attribute $attribute): RedirectResponse
    {
        DB::transaction(function () use ($request, $attribute) {
            $attribute->update(['name' => $request->validated('name')]);

            $keptIds = [];

            foreach ($request->validated('values') as $valueData) {
                if (! empty($valueData['id']) && $value = $attribute->values()->find($valueData['id'])) {
                    $value->update(['value' => $valueData['value']]);
                } else {
                    $value = $attribute->values()->create(['value' => $valueData['value']]);
                }

                $keptIds[] = $value->id;
            }

            $attribute->values()
                ->whereNotIn('id', $keptIds)
                ->whereDoesntHave('variants')
                ->delete();
        });

        return redirect()->route('admin.attributes.index')->with('success', 'Atribut berhasil diperbarui.');
    }

    public function destroy(Attribute $attribute): RedirectResponse
    {
        $attribute->delete();

        return back()->with('success', 'Atribut berhasil dihapus.');
    }
}
