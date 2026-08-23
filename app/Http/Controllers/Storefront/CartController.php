<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService) {}

    public function index(Request $request): Response
    {
        $cart = $this->cartService->currentCart($request);
        $totals = $this->cartService->totals($cart);

        return Inertia::render('Storefront/Cart', [
            'items' => $totals['items']->map(fn (CartItem $item) => [
                'id' => $item->id,
                'quantity' => $item->quantity,
                'subtotal' => $item->subtotal(),
                'variant' => [
                    'id' => $item->variant->id,
                    'price' => $item->variant->price,
                    'stock' => $item->variant->stock,
                    'label' => $item->variant->label(),
                    'product' => [
                        'name' => $item->variant->product->name,
                        'slug' => $item->variant->product->slug,
                        'image_url' => $item->variant->product->images->first()?->url(),
                    ],
                ],
            ]),
            'subtotal' => $totals['subtotal'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_variant_id' => ['required', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $variant = ProductVariant::findOrFail($validated['product_variant_id']);
        $cart = $this->cartService->currentCart($request);

        if ($variant->stock < 1) {
            return back()->with('error', 'Stok produk ini sedang habis.');
        }

        $this->cartService->addItem($cart, $variant, $validated['quantity']);

        return back()->with('success', 'Produk ditambahkan ke keranjang.');
    }

    public function update(Request $request, CartItem $item): RedirectResponse
    {
        $this->authorizeCartItem($request, $item);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $this->cartService->updateQuantity($item, $validated['quantity']);

        return back()->with('success', 'Keranjang diperbarui.');
    }

    public function destroy(Request $request, CartItem $item): RedirectResponse
    {
        $this->authorizeCartItem($request, $item);

        $this->cartService->removeItem($item);

        return back()->with('success', 'Produk dihapus dari keranjang.');
    }

    private function authorizeCartItem(Request $request, CartItem $item): void
    {
        $cart = $this->cartService->currentCart($request);

        abort_unless($item->cart_id === $cart->id, 403);
    }
}
