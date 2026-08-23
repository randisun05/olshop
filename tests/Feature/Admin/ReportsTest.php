<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_dashboard_with_analytics(): void
    {
        $admin = $this->createAdminUser();
        Order::factory()->create(['status' => OrderStatus::Completed, 'total' => 150000]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Dashboard')
            ->has('summary')
            ->has('chart')
            ->has('topProducts')
            ->has('lowStock')
        );
    }

    public function test_sales_report_only_counts_valid_order_statuses_in_range(): void
    {
        $admin = $this->createAdminUser();

        Order::factory()->create(['status' => OrderStatus::Completed, 'total' => 100000, 'created_at' => now()]);
        Order::factory()->create(['status' => OrderStatus::PendingPayment, 'total' => 999999, 'created_at' => now()]);
        Order::factory()->create(['status' => OrderStatus::Cancelled, 'total' => 999999, 'created_at' => now()]);
        Order::factory()->create(['status' => OrderStatus::Completed, 'total' => 999999, 'created_at' => now()->subYear()]);

        $response = $this->actingAs($admin)->get(route('admin.reports.sales', [
            'from' => now()->startOfMonth()->toDateString(),
            'to' => now()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Reports/Sales')
            ->where('summary.total_orders', 1)
            ->where('summary.total_revenue', 100000)
        );
    }

    public function test_top_products_report_aggregates_quantity_sold(): void
    {
        $admin = $this->createAdminUser();
        $order = Order::factory()->create(['status' => OrderStatus::Completed]);
        OrderItem::factory()->for($order)->create(['product_name' => 'Kaos Polos', 'quantity' => 3, 'subtotal' => 300000]);
        OrderItem::factory()->for($order)->create(['product_name' => 'Kaos Polos', 'quantity' => 2, 'subtotal' => 200000]);

        $response = $this->actingAs($admin)->get(route('admin.reports.top-products'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Reports/TopProducts')
            ->where('products.0.product_name', 'Kaos Polos')
            ->where('products.0.qty_sold', 5)
        );
    }

    public function test_stock_report_flags_low_stock_variants(): void
    {
        $admin = $this->createAdminUser();
        $lowStockVariant = ProductVariant::factory()->create(['stock' => 2]);
        $healthyStockVariant = ProductVariant::factory()->create(['stock' => 50]);

        $response = $this->actingAs($admin)->get(route('admin.reports.stock'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Reports/Stock')
            ->where('variants', fn ($variants) => collect($variants)->firstWhere('id', $lowStockVariant->id)['is_low'] === true
                && collect($variants)->firstWhere('id', $healthyStockVariant->id)['is_low'] === false
            )
        );
    }

    public function test_customer_cannot_access_reports(): void
    {
        $customer = $this->createCustomerUser();

        $response = $this->actingAs($customer)->get(route('admin.reports.sales'));

        $response->assertForbidden();
    }
}
