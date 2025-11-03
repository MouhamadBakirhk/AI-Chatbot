<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;

class CartController extends Controller
{
    // Display all products on the card to the user
    public function index(Request $request)
    {
        $cart = Cart::where('user_id', $request->user()->id)
                    ->with('product')
                    ->get();
        return response()->json($cart);
    }

   // Add product to card
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        try {
            $product = Product::findOrFail($request->product_id);

            
            if ($product->quantity < $request->quantity) {
                return response()->json(['success'=>false,'message'=>'Not enough stock'],400);
            }

           
            $cart = Cart::updateOrCreate(
                ['user_id' => $user->id, 'product_id' => $request->product_id],
                ['quantity' => \DB::raw('quantity + ' . $request->quantity)]
            );

           // Reduce product inventory
            $product->decrement('quantity', $request->quantity);

            return response()->json([
                'success' => true,
                'message' => 'Product added to cart',
                'cart' => $cart
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

   // Update product quantity on card
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = Cart::findOrFail($id);
        $diff = $request->quantity - $cart->quantity; //The difference between new and old
        $cart->quantity = $request->quantity;
        $cart->save();

        $product = Product::findOrFail($cart->product_id);

        if ($diff > 0) {
            if ($product->quantity < $diff) {
                return response()->json(['success'=>false,'message'=>'Not enough stock'],400);
            }
            $product->decrement('quantity', $diff);
        } elseif ($diff < 0) {
            $product->increment('quantity', abs($diff));
        }

        return response()->json([
            'success' => true,
            'message' => 'Cart updated',
            'cart' => $cart
        ]);
    }

     
    public function destroy($id)
    {
        $cart = Cart::findOrFail($id);

         
        $product = Product::findOrFail($cart->product_id);
        $product->increment('quantity', $cart->quantity);

        $cart->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product removed from cart'
        ]);
    }
}
