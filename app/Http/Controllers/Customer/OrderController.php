<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
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
}
