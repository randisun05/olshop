<?php

namespace Tests\Feature\Admin;

use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockAdjustmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_record_a_manual_stock_addition(): void
    {
        $admin = $this->createAdminUser();
        $variant = ProductVariant::factory()->create(['stock' => 10]);

        $response = $this->actingAs($admin)->post(route('admin.stock-adjustments.store'), [
            'product_variant_id' => $variant->id,
            'direction' => 'in',
            'quantity' => 5,
            'note' => 'Restock dari supplier',
        ]);

        $response->assertRedirect();
        $this->assertSame(15, $variant->fresh()->stock);
        $this->assertDatabaseHas('stock_adjustments', [
            'product_variant_id' => $variant->id,
            'type' => 'manual_in',
            'quantity_change' => 5,
            'note' => 'Restock dari supplier',
            'user_id' => $admin->id,
        ]);
    }

    public function test_admin_can_record_a_manual_stock_reduction(): void
    {
        $admin = $this->createAdminUser();
        $variant = ProductVariant::factory()->create(['stock' => 10]);

        $response = $this->actingAs($admin)->post(route('admin.stock-adjustments.store'), [
            'product_variant_id' => $variant->id,
            'direction' => 'out',
            'quantity' => 4,
            'note' => 'Barang rusak',
        ]);

        $response->assertRedirect();
        $this->assertSame(6, $variant->fresh()->stock);
        $this->assertDatabaseHas('stock_adjustments', [
            'product_variant_id' => $variant->id,
            'type' => 'manual_out',
            'quantity_change' => -4,
        ]);
    }

    public function test_manual_reduction_cannot_exceed_available_stock(): void
    {
        $admin = $this->createAdminUser();
        $variant = ProductVariant::factory()->create(['stock' => 3]);

        $response = $this->actingAs($admin)->post(route('admin.stock-adjustments.store'), [
            'product_variant_id' => $variant->id,
            'direction' => 'out',
            'quantity' => 10,
            'note' => 'Barang rusak',
        ]);

        $response->assertStatus(422);
        $this->assertSame(3, $variant->fresh()->stock);
    }

    public function test_customer_cannot_access_stock_adjustments(): void
    {
        $customer = $this->createCustomerUser();

        $response = $this->actingAs($customer)->get(route('admin.stock-adjustments.index'));

        $response->assertForbidden();
    }
}
