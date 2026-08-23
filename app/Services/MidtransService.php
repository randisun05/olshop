<?php

namespace App\Services;

use App\Models\Order;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createSnapToken(Order $order): string
    {
        $params = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (int) $order->total,
            ],
            'customer_details' => [
                'first_name' => $order->user?->name ?? $order->guest_name,
                'email' => $order->user?->email ?? $order->guest_email,
                'phone' => $order->phone,
            ],
            'item_details' => $this->buildItemDetails($order),
        ];

        return Snap::getSnapToken($params);
    }

    public function isValidSignature(string $orderNumber, string $statusCode, string $grossAmount, string $signatureKey): bool
    {
        $expected = hash('sha512', $orderNumber.$statusCode.$grossAmount.config('services.midtrans.server_key'));

        return hash_equals($expected, $signatureKey);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildItemDetails(Order $order): array
    {
        $items = $order->items->map(fn ($item) => [
            'id' => (string) $item->product_variant_id,
            'price' => (int) $item->price,
            'quantity' => $item->quantity,
            'name' => mb_substr($item->product_name.($item->variant_label !== 'Standar' ? " ({$item->variant_label})" : ''), 0, 50),
        ])->all();

        if ((float) $order->shipping_cost > 0) {
            $items[] = [
                'id' => 'shipping',
                'price' => (int) $order->shipping_cost,
                'quantity' => 1,
                'name' => 'Ongkos Kirim',
            ];
        }

        return $items;
    }
}
