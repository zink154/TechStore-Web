<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $reviews = $product->reviews()
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }

    public function store(Request $request, string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'max:1000'],
        ]);

        $existing = $product->reviews()->where('user_id', $request->user()->id)->first();
        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this product.',
            ], 422);
        }

        $review = $product->reviews()->create([
            'user_id' => $request->user()->id,
            ...$validated,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review submitted.',
            'data' => $review->load('user:id,name'),
        ], 201);
    }
}
