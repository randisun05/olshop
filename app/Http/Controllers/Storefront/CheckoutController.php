<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\PaymentMethod;
use App\Exceptions\CouponInvalidException;
use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreOrderRequest;
use App\Models\Setting;
use App\Models\ShippingZone;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\MidtransService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly CheckoutService $checkoutService,
        private readonly MidtransService $midtransService,
    ) {}

    public function create(Request $request): Response|RedirectResponse
    {
        $cart = $this->cartService->currentCart($request);
        $totals = $this->cartService->totals($cart);

        if ($totals['items']->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang belanja Anda kosong.');
        }

        return Inertia::render('Storefront/Checkout', [
            'items' => $totals['items']->map(fn ($item) => [
                'id' => $item->id,
                'quantity' => $item->quantity,
                'subtotal' => $item->subtotal(),
                'product_name' => $item->variant->product->name,
                'variant_label' => $item->variant->label(),
            ]),
            'subtotal' => $totals['subtotal'],
            'taxPercent' => (float) (Setting::get('tax_percent') ?? 0),
            'shippingZones' => ShippingZone::where('is_active', true)->orderBy('cost')->get(),
            'addresses' => $request->user()?->addresses()->orderByDesc('is_default')->get() ?? [],
        ]);
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $cart = $this->cartService->currentCart($request);
        $shippingZone = ShippingZone::findOrFail($request->validated('shipping_zone_id'));
        $user = $request->user();

        if ($request->boolean('use_saved_address') && $user) {
            $address = $user->addresses()->findOrFail($request->validated('address_id'));
            $shippingAddress = [
                'recipient_name' => $address->recipient_name,
                'phone' => $address->phone,
                'city' => $address->city,
                'postal_code' => $address->postal_code,
                'address_line' => $address->address_line,
            ];
        } else {
            $shippingAddress = [
                'recipient_name' => $request->validated('recipient_name'),
                'phone' => $request->validated('phone'),
                'city' => $request->validated('city'),
                'postal_code' => $request->validated('postal_code'),
                'address_line' => $request->validated('address_line'),
            ];
        }

        try {
            $order = $this->checkoutService->placeOrder(
                cart: $cart,
                shippingAddress: $shippingAddress,
                shippingZone: $shippingZone,
                paymentMethod: PaymentMethod::from($request->validated('payment_method')),
                user: $user,
                guest: [
                    'name' => $request->validated('guest_name'),
                    'email' => $request->validated('guest_email'),
                    'phone' => $request->validated('guest_phone'),
                ],
                notes: $request->validated('notes'),
                couponCode: $request->validated('coupon_code') ?: null,
            );
        } catch (InsufficientStockException|CouponInvalidException $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($order->payment->method === PaymentMethod::Midtrans) {
            $snapToken = $this->midtransService->createSnapToken($order);
            $order->payment->update(['provider_reference' => $snapToken]);
        }

        $email = $user?->email ?? $order->guest_email;

        return redirect()->route('order.show', ['order' => $order->order_number, 'email' => $email])
            ->with('success', 'Pesanan berhasil dibuat.');
    }
}
