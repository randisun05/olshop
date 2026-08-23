<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $products = Product::query()
            ->where('is_active', true)
            ->with(['images' => fn ($q) => $q->orderBy('sort_order')->limit(1), 'variants'])
            ->when($request->string('q')->toString(), fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->when($request->string('category')->toString(), function ($query, $slug) {
                $category = Category::where('slug', $slug)->first();

                if (! $category) {
                    return $query->whereRaw('1 = 0');
                }

                $categoryIds = [$category->id, ...$category->children()->pluck('id')];
                $query->whereIn('category_id', $categoryIds);
            })
            ->when($request->string('sort')->toString(), function ($query, $sort) {
                match ($sort) {
                    'terbaru' => $query->orderByDesc('id'),
                    'nama' => $query->orderBy('name'),
                    default => $query->orderByDesc('id'),
                };
            }, fn ($query) => $query->orderByDesc('id'))
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'image_url' => $product->images->first()?->url(),
                'min_price' => $product->minPrice(),
                'max_price' => $product->maxPrice(),
                'in_stock' => $product->totalStock() > 0,
            ]);

        return Inertia::render('Storefront/Catalog', [
            'products' => $products,
            'categories' => Category::whereNull('parent_id')->with('children')->where('is_active', true)->orderBy('name')->get(),
            'filters' => $request->only(['q', 'category', 'sort']),
        ]);
    }

    public function show(string $slug): Response
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->with(['category', 'brand', 'images', 'variants.attributeValues.attribute'])
            ->firstOrFail();

        $related = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->with(['images' => fn ($q) => $q->orderBy('sort_order')->limit(1), 'variants'])
            ->limit(4)
            ->get()
            ->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'image_url' => $p->images->first()?->url(),
                'min_price' => $p->minPrice(),
            ]);

        return Inertia::render('Storefront/ProductDetail', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'category' => $product->category?->only(['name', 'slug']),
                'brand' => $product->brand?->only(['name', 'slug']),
                'images' => $product->images->map(fn ($image) => ['id' => $image->id, 'url' => $image->url()]),
                'variants' => $product->variants->map(fn ($variant) => [
                    'id' => $variant->id,
                    'price' => $variant->price,
                    'stock' => $variant->stock,
                    'label' => $variant->label(),
                    'attribute_value_ids' => $variant->attributeValues->pluck('id'),
                ]),
            ],
            'related' => $related,
        ]);
    }
}
