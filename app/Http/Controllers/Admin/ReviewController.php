<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    public function index(): Response
    {
        $reviews = Review::with('product', 'user')
            ->orderByDesc('id')
            ->paginate(20)
            ->through(fn (Review $review) => [
                'id' => $review->id,
                'product_name' => $review->product?->name,
                'user_name' => $review->user?->name,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'created_at' => $review->created_at->toIso8601String(),
            ]);

        return Inertia::render('Admin/Reviews/Index', [
            'reviews' => $reviews,
        ]);
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return back()->with('success', 'Ulasan berhasil dihapus.');
    }
}
