<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function __construct(private readonly CheckoutService $checkoutService) {}

    public function index(Request $request): Response
    {
        $payments = Payment::query()
            ->with('order')
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Payment $payment) => [
                'id' => $payment->id,
                'order_number' => $payment->order->order_number,
                'method' => $payment->method->label(),
                'status' => $payment->status->value,
                'status_label' => $payment->status->label(),
                'amount' => $payment->amount,
                'proof_url' => $payment->proofUrl(),
                'created_at' => $payment->created_at->toIso8601String(),
            ]);

        return Inertia::render('Admin/Payments/Index', [
            'payments' => $payments,
            'filters' => $request->only('status'),
        ]);
    }

    public function verify(Payment $payment): RedirectResponse
    {
        abort_if($payment->status !== PaymentStatus::Pending, 422, 'Pembayaran ini sudah diproses.');

        $payment->update([
            'status' => PaymentStatus::Paid,
            'paid_at' => now(),
            'verified_by' => request()->user()->id,
        ]);

        $payment->order->update(['paid_at' => now()]);
        $payment->order->recordStatus(OrderStatus::Paid, 'Pembayaran manual diverifikasi admin.', request()->user()->id);

        return back()->with('success', 'Pembayaran berhasil diverifikasi.');
    }

    public function reject(Payment $payment): RedirectResponse
    {
        abort_if($payment->status !== PaymentStatus::Pending, 422, 'Pembayaran ini sudah diproses.');

        $payment->update([
            'status' => PaymentStatus::Failed,
            'verified_by' => request()->user()->id,
        ]);

        $payment->order->recordStatus(OrderStatus::Cancelled, 'Bukti pembayaran ditolak admin, stok dikembalikan.', request()->user()->id);
        $this->checkoutService->restoreStock($payment->order->load('items'));

        return back()->with('success', 'Pembayaran ditolak dan stok dikembalikan.');
    }
}
