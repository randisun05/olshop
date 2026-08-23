<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    private const SALES_STATUSES = [
        OrderStatus::Paid,
        OrderStatus::Processing,
        OrderStatus::Shipped,
        OrderStatus::Completed,
    ];

    public function __invoke(): Response
    {
        $salesStatusValues = array_map(fn (OrderStatus $s) => $s->value, self::SALES_STATUSES);
        $threshold = (int) (Setting::get('low_stock_threshold') ?? 5);

        $startOfMonth = now()->startOfMonth();

        $summary = [
            'revenue_this_month' => (float) Order::whereIn('status', $salesStatusValues)
                ->where('created_at', '>=', $startOfMonth)
                ->sum('total'),
            'orders_this_month' => Order::whereIn('status', $salesStatusValues)
                ->where('created_at', '>=', $startOfMonth)
                ->count(),
            'pending_payment' => Order::where('status', OrderStatus::PendingPayment->value)->count(),
            'low_stock_count' => ProductVariant::where('stock', '<=', $threshold)->count(),
        ];

        $since = now()->subDays(29)->startOfDay();
        $daily = Order::whereIn('status', $salesStatusValues)
            ->where('created_at', '>=', $since)
            ->select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as total'),
            ])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $chart = collect(range(0, 29))->map(function (int $i) use ($daily) {
            $date = now()->subDays(29 - $i)->toDateString();

            return [
                'date' => $date,
                'total' => (float) ($daily->get($date)->total ?? 0),
            ];
        });

        $topProducts = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('orders.status', $salesStatusValues)
            ->groupBy('order_items.product_name')
            ->orderByDesc('qty_sold')
            ->limit(5)
            ->select([
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as qty_sold'),
            ])
            ->get();

        $lowStock = ProductVariant::with('product')
            ->where('stock', '<=', $threshold)
            ->orderBy('stock')
            ->limit(5)
            ->get()
            ->map(fn (ProductVariant $variant) => [
                'product_name' => $variant->product->name,
                'variant_label' => $variant->label(),
                'stock' => $variant->stock,
            ]);

        return Inertia::render('Admin/Dashboard', [
            'summary' => $summary,
            'chart' => $chart,
            'topProducts' => $topProducts,
            'lowStock' => $lowStock,
        ]);
    }
}
