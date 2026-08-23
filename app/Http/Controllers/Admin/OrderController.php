<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(private readonly CheckoutService $checkoutService) {}

    public function index(Request $request): Response
    {
        $orders = Order::query()
            ->with('payment')
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->when($request->string('search')->toString(), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                        ->orWhere('recipient_name', 'like', "%{$search}%")
                        ->orWhere('guest_name', 'like', "%{$search}%")
                        ->orWhere('guest_email', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Order $order) => [
                'order_number' => $order->order_number,
                'customer' => $order->user?->name ?? $order->guest_name,
                'total' => $order->total,
                'status' => $order->status->value,
                'status_label' => $order->status->label(),
                'payment_status_label' => $order->payment?->status->label(),
                'created_at' => $order->created_at->toIso8601String(),
            ]);

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function show(Order $order): Response
    {
        $order->load('items', 'payment', 'shipment', 'statusHistories.changedBy', 'user');

        return Inertia::render('Admin/Orders/Show', [
            'order' => [
                'order_number' => $order->order_number,
                'status' => $order->status->value,
                'status_label' => $order->status->label(),
                'customer_name' => $order->user?->name ?? $order->guest_name,
                'customer_email' => $order->user?->email ?? $order->guest_email,
                'recipient_name' => $order->recipient_name,
                'phone' => $order->phone,
                'city' => $order->city,
                'address_line' => $order->address_line,
                'shipping_zone_name' => $order->shipping_zone_name,
                'shipping_cost' => $order->shipping_cost,
                'subtotal' => $order->subtotal,
                'discount' => $order->discount,
                'total' => $order->total,
                'notes' => $order->notes,
                'created_at' => $order->created_at->toIso8601String(),
                'items' => $order->items->map(fn ($item) => [
                    'product_name' => $item->product_name,
                    'variant_label' => $item->variant_label,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->subtotal,
                ]),
                'payment' => $order->payment ? [
                    'method_label' => $order->payment->method->label(),
                    'status_label' => $order->payment->status->label(),
                    'proof_url' => $order->payment->proofUrl(),
                ] : null,
                'shipment' => $order->shipment ? [
                    'courier' => $order->shipment->courier,
                    'tracking_number' => $order->shipment->tracking_number,
                    'shipped_at' => $order->shipment->shipped_at?->toIso8601String(),
                ] : null,
                'status_histories' => $order->statusHistories->map(fn ($h) => [
                    'status_label' => $h->status->label(),
                    'note' => $h->note,
                    'changed_by' => $h->changedBy?->name,
                    'created_at' => $h->created_at->toIso8601String(),
                ]),
            ],
        ]);
    }

    public function markProcessing(Order $order): RedirectResponse
    {
        abort_unless($order->status === OrderStatus::Paid, 422, 'Pesanan harus berstatus "Sudah Dibayar" untuk diproses.');

        $order->recordStatus(OrderStatus::Processing, 'Pesanan mulai diproses.', request()->user()->id);
        $order->sendStatusNotification();

        return back()->with('success', 'Pesanan ditandai sedang diproses.');
    }

    public function markShipped(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->status === OrderStatus::Processing, 422, 'Pesanan harus berstatus "Diproses" untuk dikirim.');

        $validated = $request->validate([
            'courier' => ['required', 'string', 'max:255'],
            'tracking_number' => ['required', 'string', 'max:255'],
        ]);

        $order->shipment()->updateOrCreate([], [
            'courier' => $validated['courier'],
            'tracking_number' => $validated['tracking_number'],
            'shipped_at' => now(),
        ]);

        $order->recordStatus(OrderStatus::Shipped, "Dikirim via {$validated['courier']}, resi {$validated['tracking_number']}.", request()->user()->id);
        $order->sendStatusNotification();

        return back()->with('success', 'Pesanan ditandai sudah dikirim.');
    }

    public function markCompleted(Order $order): RedirectResponse
    {
        abort_unless($order->status === OrderStatus::Shipped, 422, 'Pesanan harus berstatus "Dikirim" untuk diselesaikan.');

        $order->shipment()->update(['delivered_at' => now()]);
        $order->recordStatus(OrderStatus::Completed, 'Pesanan selesai.', request()->user()->id);
        $order->sendStatusNotification();

        return back()->with('success', 'Pesanan ditandai selesai.');
    }

    public function cancel(Order $order): RedirectResponse
    {
        abort_if(
            in_array($order->status, [OrderStatus::Shipped, OrderStatus::Completed, OrderStatus::Cancelled], true),
            422,
            'Pesanan pada status ini tidak dapat dibatalkan.'
        );

        $order->recordStatus(OrderStatus::Cancelled, 'Pesanan dibatalkan oleh admin, stok dikembalikan.', request()->user()->id);
        $this->checkoutService->restoreStock($order->load('items'));
        $order->sendStatusNotification();

        return back()->with('success', 'Pesanan dibatalkan dan stok dikembalikan.');
    }
}
