<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

      
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

      
        $existing = Rating::where('product_id', $validated['product_id'])
                          ->where('user_id', $user->id)
                          ->first();

        if ($existing) {
            return response()->json(['error' => 'You already rated this product'], 400);
        }

  
        $rating = Rating::create([
            'product_id' => $validated['product_id'],
            'user_id' => $user->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return response()->json(['message' => 'Rating added successfully', 'data' => $rating], 201);
    }

  
    public function show($productId)
    {
        $product = Product::with('ratings.user')->find($productId);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $average = round($product->ratings->avg('rating'), 2);

        return response()->json([
            'product_id' => $product->id,
            'average_rating' => $average,
            'ratings' => $product->ratings,
        ]);
    }
}
