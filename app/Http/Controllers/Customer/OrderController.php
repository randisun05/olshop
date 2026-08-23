<?php

namespace App\Http\Controllers\Customer;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $orders = $request->user()->orders()
            ->with('payment')
            ->orderByDesc('id')
            ->paginate(10)
            ->through(fn ($order) => [
                'order_number' => $order->order_number,
                'status_label' => $order->status->label(),
                'total' => $order->total,
                'created_at' => $order->created_at->toIso8601String(),
                'payment_status_label' => $order->payment?->status->label(),
            ]);

        return Inertia::render('Customer/Orders', [
            'orders' => $orders,
        ]);
    }

    public function confirmReceived(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        abort_unless($order->status === OrderStatus::Shipped, 422, 'Pesanan belum berstatus "Dikirim".');

        $order->shipment()->update(['delivered_at' => now()]);
        $order->recordStatus(OrderStatus::Completed, 'Pesanan dikonfirmasi diterima oleh pembeli.', $request->user()->id);

        return back()->with('success', 'Terima kasih, pesanan ditandai selesai.');
    }
}
