<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function lookupForm(): Response
    {
        return Inertia::render('Storefront/OrderLookup');
    }

    public function lookup(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_number' => ['required', 'string'],
            'email' => ['required', 'email'],
        ]);

        $order = Order::where('order_number', $validated['order_number'])
            ->where('guest_email', $validated['email'])
            ->first();

        if (! $order) {
            return back()->with('error', 'Pesanan tidak ditemukan. Periksa kembali nomor pesanan dan email Anda.');
        }

        return redirect()->route('order.show', ['order' => $order->order_number, 'email' => $order->guest_email]);
    }

    public function show(Request $request, Order $order): Response
    {
        $this->authorizeOrder($request, $order);

        $order->load('items.review', 'payment', 'shipment', 'statusHistories', 'complaints');

        $isOwner = $order->user_id !== null && $order->user_id === $request->user()?->id;
        $hasOpenComplaint = $order->complaints->contains(fn ($c) => $c->status->isOpen());

        return Inertia::render('Storefront/OrderDetail', [
            'order' => $this->transform($order),
            'email' => $request->query('email'),
            'isOwner' => $isOwner,
            'canFileComplaint' => $isOwner && $order->status === OrderStatus::Completed && ! $hasOpenComplaint,
            'midtrans' => [
                'clientKey' => config('services.midtrans.client_key'),
                'isProduction' => config('services.midtrans.is_production'),
            ],
        ]);
    }

    public function uploadProof(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($request, $order);

        $request->validate([
            'proof' => ['required', 'image', 'max:4096'],
        ]);

        if ($order->payment->method !== PaymentMethod::ManualTransfer) {
            abort(422, 'Order ini tidak menggunakan metode transfer manual.');
        }

        $path = $request->file('proof')->store('payment-proofs', 'public');
        $order->payment->update(['proof_path' => $path]);

        return back()->with('success', 'Bukti pembayaran berhasil diunggah. Tim kami akan segera memverifikasi.');
    }

    public function invoice(Request $request, Order $order): HttpResponse
    {
        $this->authorizeOrder($request, $order);

        $order->load('items', 'payment');

        $pdf = Pdf::loadView('pdf.invoice', ['order' => $order]);

        return $pdf->download("invoice-{$order->order_number}.pdf");
    }

    private function authorizeOrder(Request $request, Order $order): void
    {
        if ($order->user_id) {
            abort_unless($request->user()?->id === $order->user_id, 403);

            return;
        }

        abort_unless($request->query('email') === $order->guest_email, 403);
    }

    /**
     * @return array<string, mixed>
     */
    private function transform(Order $order): array
    {
        return [
            'order_number' => $order->order_number,
            'status' => $order->status->value,
            'status_label' => $order->status->label(),
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
            'shipment' => $order->shipment ? [
                'courier' => $order->shipment->courier,
                'tracking_number' => $order->shipment->tracking_number,
                'shipped_at' => $order->shipment->shipped_at?->toIso8601String(),
            ] : null,
            'items' => $order->items->map(fn ($item) => [
                'id' => $item->id,
                'product_name' => $item->product_name,
                'variant_label' => $item->variant_label,
                'price' => $item->price,
                'quantity' => $item->quantity,
                'subtotal' => $item->subtotal,
                'can_review' => $order->status->value === 'completed' && $item->product_id && ! $item->review,
                'reviewed' => (bool) $item->review,
            ]),
            'payment' => $order->payment ? [
                'method' => $order->payment->method->value,
                'method_label' => $order->payment->method->label(),
                'status' => $order->payment->status->value,
                'status_label' => $order->payment->status->label(),
                'amount' => $order->payment->amount,
                'provider_reference' => $order->payment->method === PaymentMethod::Midtrans
                    ? $order->payment->provider_reference
                    : null,
                'proof_url' => $order->payment->proofUrl(),
            ] : null,
            'status_histories' => $order->statusHistories->map(fn ($h) => [
                'status_label' => $h->status->label(),
                'note' => $h->note,
                'created_at' => $h->created_at->toIso8601String(),
            ]),
        ];
    }
}
