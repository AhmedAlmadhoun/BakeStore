<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // تحويل مسار الصورة إلى رابط يمكن للمتجر عرضه
    private function formatProduct(Product $product)
    {
        $data = $product->toArray();

        $data['image'] = $this->imageUrl($product->image);

        return $data;
    }

    private function imageUrl($image)
{
    if (!$image) {
        return null;
    }

    if (
        str_starts_with($image, 'http://') ||
        str_starts_with($image, 'https://') ||
        str_starts_with($image, 'data:image/')
    ) {
        return $image;
    }

    return asset('storage/' . $image);
}

    // حذف الصورة من التخزين إذا كانت صورة محلية
    private function deleteStoredImage($image)
    {
        if ($image && str_starts_with($image, 'products/')) {
            Storage::disk('public')->delete($image);
        }
    }

    // عرض المنتجات
    public function index()
{
    $products = Product::query()
        ->where('is_active', true)
        ->latest()
        ->get();

    $formattedProducts = [];

    foreach ($products as $product) {
        $formattedProducts[] = $this->formatProduct($product);
    }

    return response()->json($formattedProducts);
}

    // إضافة منتج
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'old_price' => 'nullable|numeric|min:0',
            'category' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'badge' => 'nullable|string|max:255',
            'stock' => 'sometimes|integer|min:0',
        ]);

        unset($validated['image']);

        $validated['stock'] = $validated['stock'] ?? 0;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')
                ->store('products', 'public');
        }

        $product = Product::create($validated);

        return response()->json([
            'message' => 'تمت إضافة المنتج بنجاح',
            'product' => $this->formatProduct($product),
        ], 201);
    }

    // تعديل منتج
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'old_price' => 'nullable|numeric|min:0',
            'category' => 'sometimes|required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'badge' => 'nullable|string|max:255',
            'stock' => 'sometimes|integer|min:0',
        ]);

        unset($validated['image']);

        if ($request->hasFile('image')) {
            $this->deleteStoredImage($product->image);

            $validated['image'] = $request->file('image')
                ->store('products', 'public');
        }

        $product->update($validated);

        return response()->json([
            'message' => 'تم تعديل المنتج بنجاح',
            'product' => $this->formatProduct($product->fresh()),
        ]);
    }

    // حذف منتج
    public function destroy(Product $product)
    {
        $this->deleteStoredImage($product->image);

        $product->delete();

        return response()->json([
            'message' => 'تم حذف المنتج بنجاح',
        ]);
    }
}
