<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, OrderItem $orderItem): RedirectResponse
    {
        $orderItem->loadMissing('order', 'review');

        abort_unless($orderItem->order->user_id === $request->user()->id, 403);
        abort_if($orderItem->order->status->value !== 'completed', 422, 'Pesanan belum berstatus selesai.');
        abort_if($orderItem->review, 422, 'Item ini sudah pernah diulas.');
        abort_unless($orderItem->product_id, 422, 'Produk pada item ini sudah tidak tersedia.');

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $orderItem->review()->create([
            'product_id' => $orderItem->product_id,
            'user_id' => $request->user()->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return back()->with('success', 'Terima kasih atas ulasan Anda.');
    }
}
