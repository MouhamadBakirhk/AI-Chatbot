<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
class OrderController extends Controller
{
    // --- Existing CRUD ---

    public function index() {
        return response()->json(Order::with('user')->get());
    }

    public function show($id) {
        $order = Order::with('user','items.product')->findOrFail($id);
        return response()->json($order);
    }

    public function store(Request $request) {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'total' => 'required|numeric',
            'status' => 'nullable|in:pending,processing,completed,canceled'
        ]);

        $order = Order::create($request->all());
        return response()->json($order, 201);
    }

    public function update(Request $request, $id) {
        $order = Order::findOrFail($id);
        $order->update($request->all());
        return response()->json($order);
    }

    public function destroy($id) {
        $order = Order::findOrFail($id);
        $order->delete();
        return response()->json(['message' => 'Order deleted']);
    }

    // --- New Checkout Function ---
    public function checkout(Request $request)
{
    $user = $request->user();
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User not authenticated'
        ], 401);
    }

    $cartItems = Cart::where('user_id', $user->id)->with('product')->get();
    if ($cartItems->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'Cart is empty'
        ], 400);
    }

    try {
        $order = DB::transaction(function () use ($user, $cartItems) {
            $order = Order::create([
                'user_id' => $user->id,
                'total'   => $cartItems->sum(fn($item) => $item->product->price * $item->quantity),
                'status'  => 'pending',
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item->product_id,
                    'quantity'   => $item->quantity,
                    'price'      => $item->product->price,
                ]);
            }

            Cart::where('user_id', $user->id)->delete();

            return $order;
        });

        return response()->json([
            'success' => true,
            'message' => 'Order placed successfully!',
            'order'   => $order->load('items.product')
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error'   => $e->getMessage()
        ], 500);
    }
}
}
