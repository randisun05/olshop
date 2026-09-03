<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StockAdjustmentType;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockAdjustment;
use App\Services\StockNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class StockAdjustmentController extends Controller
{
    public function index(Request $request): Response
    {
        $adjustments = StockAdjustment::query()
            ->with(['variant.product', 'user'])
            ->when($request->integer('variant_id'), fn ($q, $variantId) => $q->where('product_variant_id', $variantId))
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (StockAdjustment $adjustment) => [
                'id' => $adjustment->id,
                'product_name' => $adjustment->variant->product->name,
                'variant_label' => $adjustment->variant->label(),
                'type_label' => $adjustment->type->label(),
                'quantity_change' => $adjustment->quantity_change,
                'note' => $adjustment->note,
                'user' => $adjustment->user?->name ?? 'Sistem',
                'created_at' => $adjustment->created_at->format('d/m/Y H:i'),
            ]);

        return Inertia::render('Admin/StockAdjustments/Index', [
            'adjustments' => $adjustments,
            'products' => Product::with('variants')->orderBy('name')->get(['id', 'name'])->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'variants' => $product->variants->map(fn (ProductVariant $variant) => [
                    'id' => $variant->id,
                    'label' => $variant->label().' ('.$variant->sku.') — stok: '.$variant->stock,
                ]),
            ]),
            'filters' => $request->only('variant_id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_variant_id' => ['required', Rule::exists('product_variants', 'id')],
            'direction' => ['required', Rule::in(['in', 'out'])],
            'quantity' => ['required', 'integer', 'min:1'],
            'note' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            /** @var ProductVariant $variant */
            $variant = ProductVariant::lockForUpdate()->findOrFail($validated['product_variant_id']);
            $stockBefore = $variant->stock;

            $isOut = $validated['direction'] === 'out';

            if ($isOut && $variant->stock < $validated['quantity']) {
                abort(422, 'Stok tidak mencukupi untuk pengurangan ini.');
            }

            $change = $isOut ? -$validated['quantity'] : $validated['quantity'];
            $variant->increment('stock', $change);

            StockAdjustment::log(
                $variant,
                $isOut ? StockAdjustmentType::ManualOut : StockAdjustmentType::ManualIn,
                $change,
                $validated['note'],
                $request->user(),
            );

            if ($isOut) {
                StockNotifier::checkLowStock($variant, $stockBefore);
            }
        });

        return back()->with('success', 'Penyesuaian stok berhasil dicatat.');
    }
}
