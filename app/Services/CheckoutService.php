<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Exceptions\InsufficientStockException;
use App\Models\Cart;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\ShippingZone;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutService
{
    /**
     * @param  array{recipient_name: string, phone: string, city: string, postal_code: ?string, address_line: string}  $shippingAddress
     * @param  array{name: ?string, email: ?string, phone: ?string}  $guest
     *
     * @throws InsufficientStockException
     */
    public function placeOrder(
        Cart $cart,
        array $shippingAddress,
        ShippingZone $shippingZone,
        PaymentMethod $paymentMethod,
        ?User $user,
        array $guest = [],
        ?string $notes = null,
    ): Order {
        return DB::transaction(function () use ($cart, $shippingAddress, $shippingZone, $paymentMethod, $user, $guest, $notes) {
            $cart->loadMissing('items');

            if ($cart->items->isEmpty()) {
                throw new \RuntimeException('Keranjang belanja kosong.');
            }

            $subtotal = 0;
            $orderItemsData = [];

            foreach ($cart->items as $cartItem) {
                /** @var ProductVariant $variant */
                $variant = ProductVariant::with('product', 'attributeValues')
                    ->lockForUpdate()
                    ->findOrFail($cartItem->product_variant_id);

                if ($variant->stock < $cartItem->quantity) {
                    throw new InsufficientStockException($variant->product->name);
                }

                $lineSubtotal = $variant->price * $cartItem->quantity;
                $subtotal += $lineSubtotal;

                $orderItemsData[] = [
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $variant->product->name,
                    'variant_label' => $variant->label(),
                    'price' => $variant->price,
                    'quantity' => $cartItem->quantity,
                    'subtotal' => $lineSubtotal,
                ];

                $variant->decrement('stock', $cartItem->quantity);
            }

            $total = $subtotal + $shippingZone->cost;

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $user?->id,
                'guest_name' => $user ? null : ($guest['name'] ?? null),
                'guest_email' => $user ? null : ($guest['email'] ?? null),
                'guest_phone' => $user ? null : ($guest['phone'] ?? null),
                'status' => OrderStatus::PendingPayment,
                'recipient_name' => $shippingAddress['recipient_name'],
                'phone' => $shippingAddress['phone'],
                'city' => $shippingAddress['city'],
                'postal_code' => $shippingAddress['postal_code'] ?? null,
                'address_line' => $shippingAddress['address_line'],
                'shipping_zone_name' => $shippingZone->name,
                'shipping_cost' => $shippingZone->cost,
                'subtotal' => $subtotal,
                'total' => $total,
                'notes' => $notes,
            ]);

            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
            }

            $order->payment()->create([
                'method' => $paymentMethod,
                'status' => PaymentStatus::Pending,
                'amount' => $total,
            ]);

            $order->recordStatus(OrderStatus::PendingPayment, 'Pesanan dibuat.', $user?->id);

            $cart->items()->delete();

            return $order->load('items', 'payment');
        });
    }

    public function restoreStock(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    ProductVariant::whereKey($item->product_variant_id)->increment('stock', $item->quantity);
                }
            }
        });
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
