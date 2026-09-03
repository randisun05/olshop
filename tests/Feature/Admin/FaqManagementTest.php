<?php

namespace Tests\Feature\Admin;

use App\Models\FaqEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_faq_entry(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->post(route('admin.faq.store'), [
            'question' => 'Apakah ada garansi?',
            'answer' => 'Ya, sesuai ketentuan masing-masing produk.',
            'keywords' => 'garansi, jaminan',
            'category' => 'Produk',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.faq.index'));
        $this->assertDatabaseHas('faq_entries', ['question' => 'Apakah ada garansi?']);
    }

    public function test_admin_can_update_faq_entry(): void
    {
        $admin = $this->createAdminUser();
        $entry = FaqEntry::factory()->create(['question' => 'Lama']);

        $response = $this->actingAs($admin)->put(route('admin.faq.update', $entry), [
            'question' => 'Baru',
            'answer' => $entry->answer,
            'keywords' => $entry->keywords,
            'sort_order' => 0,
            'is_active' => false,
        ]);

        $response->assertRedirect(route('admin.faq.index'));
        $this->assertSame('Baru', $entry->fresh()->question);
        $this->assertFalse($entry->fresh()->is_active);
    }

    public function test_admin_can_delete_faq_entry(): void
    {
        $admin = $this->createAdminUser();
        $entry = FaqEntry::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.faq.destroy', $entry));

        $response->assertRedirect();
        $this->assertDatabaseMissing('faq_entries', ['id' => $entry->id]);
    }

    public function test_customer_cannot_manage_faq_entries(): void
    {
        $customer = $this->createCustomerUser();

        $response = $this->actingAs($customer)->get(route('admin.faq.index'));

        $response->assertForbidden();
    }

    public function test_staff_gudang_cannot_manage_faq_entries(): void
    {
        $staff = $this->createStaffGudangUser();

        $response = $this->actingAs($staff)->get(route('admin.faq.index'));

        $response->assertForbidden();
    }
}
