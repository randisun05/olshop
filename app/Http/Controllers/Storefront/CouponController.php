<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function __construct(private readonly CartService $cartService) {}

    public function check(Request $request): JsonResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        $cart = $this->cartService->currentCart($request);
        $subtotal = $this->cartService->totals($cart)['subtotal'];

        $coupon = Coupon::where('code', $request->string('code'))->first();

        if (! $coupon || ! $coupon->isValidFor($subtotal)) {
            return response()->json([
                'valid' => false,
                'message' => 'Kode kupon tidak valid, sudah kedaluwarsa, atau belanja belum memenuhi minimum.',
            ]);
        }

        return response()->json([
            'valid' => true,
            'discount' => $coupon->calculateDiscount($subtotal),
            'message' => 'Kupon berhasil diterapkan.',
        ]);
    }
}
