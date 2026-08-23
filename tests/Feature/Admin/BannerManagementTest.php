<?php

namespace Tests\Feature\Admin;

use App\Models\Banner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BannerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_banner(): void
    {
        Storage::fake('public');
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->post(route('admin.banners.store'), [
            'title' => 'Promo Awal Tahun',
            'image' => UploadedFile::fake()->image('banner.jpg'),
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.banners.index'));
        $this->assertDatabaseHas('banners', ['title' => 'Promo Awal Tahun']);
        Storage::disk('public')->assertExists(Banner::first()->image);
    }

    public function test_customer_cannot_manage_banners(): void
    {
        $customer = $this->createCustomerUser();

        $response = $this->actingAs($customer)->get(route('admin.banners.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_delete_banner(): void
    {
        Storage::fake('public');
        $admin = $this->createAdminUser();
        $banner = Banner::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.banners.destroy', $banner));

        $response->assertRedirect();
        $this->assertDatabaseMissing('banners', ['id' => $banner->id]);
    }
}
