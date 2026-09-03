<?php

namespace Tests\Feature\Admin;

use App\Exports\BrandTemplateExport;
use App\Exports\CategoryTemplateExport;
use App\Exports\ProductTemplateExport;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Excel as ExcelWriterType;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ImportManagementTest extends TestCase
{
    use RefreshDatabase;

    private function fakeXlsxUpload(object $export, string $filename): UploadedFile
    {
        $bytes = Excel::raw($export, ExcelWriterType::XLSX);

        return UploadedFile::fake()->createWithContent($filename, $bytes);
    }

    public function test_admin_can_import_categories_from_the_template_file(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->post(route('admin.imports.categories'), [
            'file' => $this->fakeXlsxUpload(new CategoryTemplateExport, 'kategori.xlsx'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', ['name' => 'Elektronik', 'parent_id' => null]);

        $parent = Category::where('name', 'Elektronik')->first();
        $this->assertDatabaseHas('categories', ['name' => 'Handphone', 'parent_id' => $parent->id]);
    }

    public function test_admin_can_import_brands_from_the_template_file(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->post(route('admin.imports.brands'), [
            'file' => $this->fakeXlsxUpload(new BrandTemplateExport, 'brand.xlsx'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('brands', ['name' => 'Samsung']);
        $this->assertDatabaseHas('brands', ['name' => 'Xiaomi']);
    }

    public function test_admin_can_import_simple_products_when_category_and_brand_already_exist(): void
    {
        $admin = $this->createAdminUser();
        Category::factory()->create(['name' => 'Elektronik', 'slug' => 'elektronik']);
        Brand::factory()->create(['name' => 'Samsung', 'slug' => 'samsung']);

        $response = $this->actingAs($admin)->post(route('admin.imports.products'), [
            'file' => $this->fakeXlsxUpload(new ProductTemplateExport, 'produk.xlsx'),
        ]);

        $response->assertRedirect();

        $product = Product::where('slug', 'rak-serbaguna-3-susun')->first();
        $this->assertNotNull($product);
        $this->assertSame(1, $product->variants()->count());
        $this->assertEquals(129000, $product->variants()->first()->price);
        $this->assertSame(20, $product->variants()->first()->stock);

        $this->assertDatabaseHas('products', ['slug' => 'kabel-data-usb-c']);
    }

    public function test_product_import_reports_row_error_when_category_is_missing(): void
    {
        $admin = $this->createAdminUser();
        // Sengaja tidak membuat kategori "Elektronik" agar baris impor gagal tervalidasi.

        $response = $this->actingAs($admin)->post(route('admin.imports.products'), [
            'file' => $this->fakeXlsxUpload(new ProductTemplateExport, 'produk.xlsx'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('products', ['slug' => 'rak-serbaguna-3-susun']);
    }

    public function test_import_rejects_a_file_larger_than_the_size_limit(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->post(route('admin.imports.categories'), [
            'file' => UploadedFile::fake()->create('kategori.xlsx', 5121),
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_customer_cannot_access_import_page(): void
    {
        $customer = $this->createCustomerUser();

        $response = $this->actingAs($customer)->get(route('admin.imports.index'));

        $response->assertForbidden();
    }
}
