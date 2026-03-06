<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Product;
use Cloudinary\Cloudinary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = Product::with('category');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
            );
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $sortBy = in_array($request->input('sort_by'), ['name', 'price', 'stock', 'created_at'])
            ? $request->input('sort_by') : 'created_at';
        $sortDir = $request->input('sort_dir') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortDir);

        $products = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['boolean'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('image')) {
            try {
                $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
                $result = $cloudinary->uploadApi()->upload($request->file('image')->getRealPath(), [
                    'folder' => 'techstore/products',
                ]);
                $validated['image_url'] = $result['secure_url'];
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Image upload failed: ' . $e->getMessage(),
                ], 422);
            }
        }

        unset($validated['image']);
        $product = Product::create($validated);

        ActivityLog::log('created', 'Product', $product->id, "Created product: {$product->name}");

        return response()->json([
            'success' => true,
            'message' => 'Product created.',
            'data' => $product->load('category'),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $product = Product::with('category')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'category_id' => ['sometimes', 'exists:categories,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['boolean'],
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if ($request->hasFile('image')) {
            try {
                $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
                $result = $cloudinary->uploadApi()->upload($request->file('image')->getRealPath(), [
                    'folder' => 'techstore/products',
                ]);
                $validated['image_url'] = $result['secure_url'];
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Image upload failed: ' . $e->getMessage(),
                ], 422);
            }
        }

        unset($validated['image']);
        $product->update($validated);

        ActivityLog::log('updated', 'Product', $product->id, "Updated product: {$product->name}");

        return response()->json([
            'success' => true,
            'message' => 'Product updated.',
            'data' => $product->fresh()->load('category'),
        ]);
    }

    public function toggleFeatured(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->update(['is_featured' => !$product->is_featured]);

        ActivityLog::log('updated', 'Product', $product->id,
            ($product->is_featured ? 'Featured' : 'Unfeatured') . " product: {$product->name}");

        return response()->json([
            'success' => true,
            'message' => $product->is_featured ? 'Product featured.' : 'Product unfeatured.',
            'data' => $product->fresh()->load('category'),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $name = $product->name;
        $product->delete();

        ActivityLog::log('deleted', 'Product', $id, "Deleted product: {$name}");

        return response()->json([
            'success' => true,
            'message' => 'Product deleted.',
        ]);
    }

    public function duplicate(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $newProduct = $product->replicate();
        $newProduct->name = $product->name . ' (Copy)';
        $newProduct->slug = Str::slug($newProduct->name) . '-' . time();
        $newProduct->is_active = false;
        $newProduct->save();

        ActivityLog::log('created', 'Product', $newProduct->id, "Duplicated product: {$product->name}");

        return response()->json([
            'success' => true,
            'message' => 'Product duplicated.',
            'data' => $newProduct->load('category'),
        ], 201);
    }

    public function bulkAction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:products,id'],
            'action' => ['required', 'in:delete,activate,deactivate'],
        ]);

        $query = Product::whereIn('id', $validated['ids']);
        $count = count($validated['ids']);

        match ($validated['action']) {
            'delete' => $query->delete(),
            'activate' => $query->update(['is_active' => true]),
            'deactivate' => $query->update(['is_active' => false]),
        };

        ActivityLog::log('bulk', 'Product', 0,
            ucfirst($validated['action']) . " {$count} products");

        return response()->json([
            'success' => true,
            'message' => "Bulk {$validated['action']} completed for {$count} products.",
        ]);
    }
}
