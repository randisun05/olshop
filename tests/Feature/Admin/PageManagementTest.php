<?php

namespace Tests\Feature\Admin;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_page(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->post(route('admin.pages.store'), [
            'title' => 'Syarat & Ketentuan',
            'slug' => 'syarat-ketentuan',
            'content' => 'Isi halaman syarat dan ketentuan.',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.pages.index'));
        $this->assertDatabaseHas('pages', ['slug' => 'syarat-ketentuan']);
    }

    public function test_customer_cannot_manage_pages(): void
    {
        $customer = $this->createCustomerUser();

        $response = $this->actingAs($customer)->get(route('admin.pages.index'));

        $response->assertForbidden();
    }

    public function test_public_can_view_active_page(): void
    {
        $page = Page::factory()->create(['slug' => 'faq', 'is_active' => true]);

        $response = $this->get(route('page.show', $page->slug));

        $response->assertOk();
        $response->assertInertia(fn ($p) => $p->component('Storefront/Page')->where('page.title', $page->title));
    }

    public function test_inactive_page_returns_404(): void
    {
        $page = Page::factory()->create(['slug' => 'draft', 'is_active' => false]);

        $response = $this->get(route('page.show', $page->slug));

        $response->assertNotFound();
    }
}
