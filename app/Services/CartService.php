<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function currentCart(Request $request): Cart
    {
        if ($request->user()) {
            return Cart::firstOrCreate(['user_id' => $request->user()->id]);
        }

        return Cart::firstOrCreate(['session_id' => $request->session()->getId()]);
    }

    /**
     * Hitung jumlah item cart tanpa membuat baris cart baru (dipakai untuk
     * lencana keranjang di header, agar pengunjung yang belum pernah
     * menambah apa pun tidak membuat baris cart kosong di database).
     */
    public function currentCartItemCount(Request $request): int
    {
        $cart = $request->user()
            ? Cart::where('user_id', $request->user()->id)->first()
            : Cart::where('session_id', $request->session()->getId())->first();

        return $cart ? (int) $cart->items()->sum('quantity') : 0;
    }

    public function addItem(Cart $cart, ProductVariant $variant, int $quantity): CartItem
    {
        return DB::transaction(function () use ($cart, $variant, $quantity) {
            $item = $cart->items()->where('product_variant_id', $variant->id)->lockForUpdate()->first();

            $newQuantity = min(($item?->quantity ?? 0) + $quantity, $variant->stock);
            $newQuantity = max($newQuantity, 0);

            if ($item) {
                $item->update(['quantity' => $newQuantity]);

                return $item;
            }

            return $cart->items()->create([
                'product_variant_id' => $variant->id,
                'quantity' => $newQuantity,
            ]);
        });
    }

    public function updateQuantity(CartItem $item, int $quantity): void
    {
        $quantity = max(1, min($quantity, $item->variant->stock));

        $item->update(['quantity' => $quantity]);
    }

    public function removeItem(CartItem $item): void
    {
        $item->delete();
    }

    /**
     * Gabungkan cart tamu (berdasarkan session lama, sebelum regenerasi saat login)
     * ke cart milik user yang baru saja login.
     */
    public function mergeGuestCartIntoUser(string $sessionId, User $user): void
    {
        $guestCart = Cart::where('session_id', $sessionId)->first();

        if (! $guestCart) {
            return;
        }

        $userCart = Cart::firstOrCreate(['user_id' => $user->id]);

        DB::transaction(function () use ($guestCart, $userCart) {
            foreach ($guestCart->items as $guestItem) {
                $existing = $userCart->items()->where('product_variant_id', $guestItem->product_variant_id)->first();

                if ($existing) {
                    $existing->update([
                        'quantity' => min($existing->quantity + $guestItem->quantity, $guestItem->variant->stock),
                    ]);
                } else {
                    $userCart->items()->create([
                        'product_variant_id' => $guestItem->product_variant_id,
                        'quantity' => $guestItem->quantity,
                    ]);
                }
            }

            $guestCart->delete();
        });
    }

    public function totals(Cart $cart): array
    {
        $cart->loadMissing('items.variant.product', 'items.variant.attributeValues');

        $subtotal = $cart->items->sum(fn (CartItem $item) => $item->subtotal());

        return [
            'items' => $cart->items,
            'subtotal' => $subtotal,
        ];
    }
}
