<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\ActivityLog;
use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService) {}

    public function index(Request $request): Response
    {
        $products = Product::query()
            ->with(['category', 'brand', 'variants'])
            ->when($request->string('search')->toString(), fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->when($request->integer('category_id'), fn ($q, $categoryId) => $q->where('category_id', $categoryId))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'category' => $product->category?->name,
                'brand' => $product->brand?->name,
                'is_active' => $product->is_active,
                'min_price' => $product->minPrice(),
                'max_price' => $product->maxPrice(),
                'total_stock' => $product->totalStock(),
            ]);

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['search', 'category_id']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Products/Form', $this->formProps());
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['images', 'variants']);

        $product = $this->productService->create(
            [...$data, 'is_active' => $request->boolean('is_active', true), 'is_featured' => $request->boolean('is_featured')],
            $request->file('images', []),
            $request->input('variants', []),
        );

        return redirect()->route('admin.products.edit', $product)->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product): Response
    {
        $product->load(['images', 'variants.attributeValues.attribute']);

        return Inertia::render('Admin/Products/Form', [
            ...$this->formProps(),
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'category_id' => $product->category_id,
                'brand_id' => $product->brand_id,
                'description' => $product->description,
                'weight' => $product->weight,
                'is_active' => $product->is_active,
                'is_featured' => $product->is_featured,
                'images' => $product->images->map(fn ($image) => [
                    'id' => $image->id,
                    'url' => $image->url(),
                ]),
                'variants' => $product->variants->map(fn ($variant) => [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'price' => $variant->price,
                    'stock' => $variant->stock,
                    'attribute_value_ids' => $variant->attributeValues->pluck('id'),
                ]),
            ],
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->safe()->except(['images', 'variants', 'delete_image_ids']);

        $this->productService->update(
            $product,
            [...$data, 'is_active' => $request->boolean('is_active'), 'is_featured' => $request->boolean('is_featured')],
            $request->file('images', []),
            $request->input('delete_image_ids', []),
            $request->input('variants', []),
        );

        return redirect()->route('admin.products.edit', $product)->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        ActivityLog::record('product.delete', "Menghapus produk \"{$product->name}\".", $product);

        $this->productService->delete($product);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formProps(): array
    {
        return [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'brands' => Brand::orderBy('name')->get(['id', 'name']),
            'attributes' => Attribute::with('values')->orderBy('name')->get(),
        ];
    }
}
