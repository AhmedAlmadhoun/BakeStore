<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Product;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('items')
            ->latest()
            ->get();

        return response()->json($orders);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:new,processing,completed,cancelled',
        ]);

        $order->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'message' => 'تم تحديث حالة الطلب بنجاح',
            'order' => $order,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:30',
            'customer_address' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $order = DB::transaction(function () use ($validated) {
            $total = 0;
            $orderItems = [];

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                if (!$product->is_active) {
                    abort(422, 'أحد المنتجات غير متاح حالياً');
                }

                $price = $product->price;
                $quantity = $item['quantity'];

                $total += $price * $quantity;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $price,
                    'quantity' => $quantity,
                ];
            }

            $order = Order::create([
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_address' => $validated['customer_address'],
                'total' => $total,
                'status' => 'new',
            ]);

            $order->items()->createMany($orderItems);

            return $order;
        });

        return response()->json([
            'message' => 'تم تسجيل الطلب بنجاح',
            'order' => $order->load('items'),
        ], 201);
    }
}
