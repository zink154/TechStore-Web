<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $wishlistItems = $request->user()
            ->wishlists()
            ->with('product.category')
            ->orderByDesc('created_at')
            ->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $wishlistItems,
        ]);
    }

    public function toggle(Request $request, int $productId): JsonResponse
    {
        $exists = $request->user()->wishlists()->where('product_id', $productId)->first();

        if ($exists) {
            $exists->delete();
            return response()->json([
                'success' => true,
                'message' => 'Removed from wishlist.',
                'data' => ['wishlisted' => false],
            ]);
        }

        $request->user()->wishlists()->create(['product_id' => $productId]);

        return response()->json([
            'success' => true,
            'message' => 'Added to wishlist.',
            'data' => ['wishlisted' => true],
        ], 201);
    }

    public function check(Request $request): JsonResponse
    {
        $productIds = explode(',', $request->query('product_ids', ''));
        $productIds = array_filter(array_map('intval', $productIds));

        $wishlisted = $request->user()
            ->wishlists()
            ->whereIn('product_id', $productIds)
            ->pluck('product_id')
            ->toArray();

        return response()->json([
            'success' => true,
            'data' => $wishlisted,
        ]);
    }
}
