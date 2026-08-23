<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Exports\SalesExport;
use App\Exports\StockExport;
use App\Exports\TopProductsExport;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    /**
     * Status pesanan yang dianggap sebagai penjualan sah (bukan menunggu bayar/dibatalkan).
     */
    private const SALES_STATUSES = [
        OrderStatus::Paid,
        OrderStatus::Processing,
        OrderStatus::Shipped,
        OrderStatus::Completed,
    ];

    public function sales(Request $request): Response
    {
        [$from, $to] = $this->dateRange($request);

        $orders = $this->salesQuery($from, $to)->latest()->get();

        return Inertia::render('Admin/Reports/Sales', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'summary' => [
                'total_orders' => $orders->count(),
                'total_revenue' => $orders->sum('total'),
            ],
            'orders' => $orders->map(fn (Order $order) => [
                'order_number' => $order->order_number,
                'date' => $order->created_at->format('d/m/Y H:i'),
                'customer' => $order->user->name ?? $order->guest_name,
                'status' => $order->status->label(),
                'total' => (float) $order->total,
            ]),
        ]);
    }

    public function salesExportExcel(Request $request): RedirectResponse|BinaryFileResponse
    {
        [$from, $to] = $this->dateRange($request);

        $orders = $this->salesQuery($from, $to)->latest()->get();

        return Excel::download(new SalesExport($orders), "laporan-penjualan-{$from->toDateString()}-{$to->toDateString()}.xlsx");
    }

    public function salesExportPdf(Request $request): HttpResponse
    {
        [$from, $to] = $this->dateRange($request);

        $orders = $this->salesQuery($from, $to)->latest()->get();

        $pdf = Pdf::loadView('pdf.report-sales', [
            'orders' => $orders,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);

        return $pdf->download("laporan-penjualan-{$from->toDateString()}-{$to->toDateString()}.pdf");
    }

    public function topProducts(Request $request): Response
    {
        [$from, $to] = $this->dateRange($request);

        $rows = $this->topProductsQuery($from, $to)->get();

        return Inertia::render('Admin/Reports/TopProducts', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'products' => $rows,
        ]);
    }

    public function topProductsExportExcel(Request $request): BinaryFileResponse
    {
        [$from, $to] = $this->dateRange($request);

        $rows = $this->topProductsQuery($from, $to)->get();

        return Excel::download(new TopProductsExport($rows), "produk-terlaris-{$from->toDateString()}-{$to->toDateString()}.xlsx");
    }

    public function stock(Request $request): Response
    {
        $threshold = (int) (Setting::get('low_stock_threshold') ?? 5);

        $variants = ProductVariant::with(['product', 'attributeValues'])
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->orderBy('product_variants.stock')
            ->select('product_variants.*')
            ->get();

        return Inertia::render('Admin/Reports/Stock', [
            'threshold' => $threshold,
            'variants' => $variants->map(fn (ProductVariant $variant) => [
                'id' => $variant->id,
                'product_name' => $variant->product->name,
                'variant_label' => $variant->label(),
                'sku' => $variant->sku,
                'price' => (float) $variant->price,
                'stock' => $variant->stock,
                'is_low' => $variant->stock <= $threshold,
            ]),
        ]);
    }

    public function stockExportExcel(): BinaryFileResponse
    {
        $variants = ProductVariant::with(['product', 'attributeValues'])->orderBy('stock')->get();

        return Excel::download(new StockExport($variants), 'laporan-stok-'.now()->toDateString().'.xlsx');
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function dateRange(Request $request): array
    {
        $from = $request->filled('from') ? Carbon::parse($request->string('from')) : now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->string('to')) : now();

        return [$from->startOfDay(), $to->endOfDay()];
    }

    private function salesQuery(Carbon $from, Carbon $to): Builder
    {
        return Order::with('user')
            ->whereIn('status', array_map(fn (OrderStatus $s) => $s->value, self::SALES_STATUSES))
            ->whereBetween('created_at', [$from, $to]);
    }

    private function topProductsQuery(Carbon $from, Carbon $to): Builder
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('orders.status', array_map(fn (OrderStatus $s) => $s->value, self::SALES_STATUSES))
            ->whereBetween('orders.created_at', [$from, $to])
            ->groupBy('order_items.product_name')
            ->orderByDesc('qty_sold')
            ->limit(20)
            ->select([
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as qty_sold'),
                DB::raw('SUM(order_items.subtotal) as revenue'),
            ]);
    }
}
