<?php

namespace App\Http\Controllers\Public;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\CheckoutService;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransNotificationController extends Controller
{
    public function __construct(
        private readonly MidtransService $midtransService,
        private readonly CheckoutService $checkoutService,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $orderNumber = (string) $request->input('order_id');
        $statusCode = (string) $request->input('status_code');
        $grossAmount = (string) $request->input('gross_amount');
        $signatureKey = (string) $request->input('signature_key');
        $transactionStatus = (string) $request->input('transaction_status');
        $fraudStatus = (string) $request->input('fraud_status');

        if (! $this->midtransService->isValidSignature($orderNumber, $statusCode, $grossAmount, $signatureKey)) {
            Log::warning('Midtrans webhook: invalid signature', ['order_id' => $orderNumber]);

            return response()->json(['message' => 'invalid signature'], 403);
        }

        $order = Order::where('order_number', $orderNumber)->with('payment', 'items')->first();

        if (! $order || ! $order->payment) {
            return response()->json(['message' => 'order not found'], 404);
        }

        // Idempoten: notifikasi yang sudah diproses (order tidak lagi pending) diabaikan.
        if ($order->status !== OrderStatus::PendingPayment) {
            return response()->json(['message' => 'already processed']);
        }

        match (true) {
            in_array($transactionStatus, ['capture', 'settlement'], true) && $fraudStatus !== 'deny' => $this->markPaid($order),
            in_array($transactionStatus, ['deny', 'cancel', 'expire', 'failure'], true) => $this->markFailed($order, $transactionStatus),
            default => null,
        };

        return response()->json(['message' => 'ok']);
    }

    private function markPaid(Order $order): void
    {
        $order->payment->update(['status' => PaymentStatus::Paid, 'paid_at' => now()]);
        $order->update(['paid_at' => now()]);
        $order->recordStatus(OrderStatus::Paid, 'Pembayaran Midtrans diterima.');
    }

    private function markFailed(Order $order, string $transactionStatus): void
    {
        $status = $transactionStatus === 'expire' ? PaymentStatus::Expired : PaymentStatus::Failed;

        $order->payment->update(['status' => $status]);
        $order->recordStatus(OrderStatus::Cancelled, "Pembayaran {$transactionStatus}, stok dikembalikan.");
        $this->checkoutService->restoreStock($order);
    }
}
