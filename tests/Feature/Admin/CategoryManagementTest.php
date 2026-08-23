<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_category(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Elektronik',
            'slug' => 'elektronik',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', ['slug' => 'elektronik']);
    }

    public function test_customer_cannot_manage_categories(): void
    {
        $customer = $this->createCustomerUser();

        $response = $this->actingAs($customer)->get(route('admin.categories.index'));

        $response->assertForbidden();
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $admin = $this->createAdminUser();
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($admin)->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }
}
