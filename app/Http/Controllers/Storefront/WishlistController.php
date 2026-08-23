<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WishlistController extends Controller
{
    public function index(Request $request): Response
    {
        $products = $request->user()->wishlists()
            ->with(['product.images' => fn ($q) => $q->orderBy('sort_order')->limit(1), 'product.variants'])
            ->latest()
            ->get()
            ->pluck('product')
            ->filter()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'image_url' => $product->images->first()?->url(),
                'min_price' => $product->minPrice(),
                'in_stock' => $product->totalStock() > 0,
            ]);

        return Inertia::render('Customer/Wishlist', [
            'products' => $products->values(),
        ]);
    }

    public function toggle(Request $request, Product $product): RedirectResponse
    {
        $wishlist = $request->user()->wishlists()->where('product_id', $product->id)->first();

        if ($wishlist) {
            $wishlist->delete();

            return back()->with('success', 'Dihapus dari wishlist.');
        }

        $request->user()->wishlists()->create(['product_id' => $product->id]);

        return back()->with('success', 'Ditambahkan ke wishlist.');
    }
}
