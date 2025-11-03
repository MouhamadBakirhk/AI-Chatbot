<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    // Show all products
    public function index()
    {
        return response()->json(Product::all());
    }

    // Show single product
    public function show($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }
        return response()->json($product);
    }

    // Add product (Admin)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'price' => 'required|numeric',
            'quantity' => 'required|integer',
            'image' => 'required|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        $imagePath = $request->file('image')->store('products', 'public');

        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'category' => $request->category,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'image' => $imagePath,
        ]);

        return response()->json(['success' => true, 'message' => 'Product added successfully', 'product' => $product]);
    }

    // Update product (Admin)
    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) return response()->json(['success' => false, 'message' => 'Product not found'], 404);

        foreach (['name','description','category','price','quantity'] as $field) {
            if ($request->has($field)) {
                $product->$field = $request->$field;
            }
        }

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $product->image = $imagePath;
        }

        $product->save();

        return response()->json(['success' => true, 'message' => 'Product updated successfully', 'product' => $product]);
    }

    // Delete product (Admin)
    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) return response()->json(['success' => false, 'message' => 'Product not found'], 404);

        $product->delete();
        return response()->json(['success' => true, 'message' => 'Product deleted successfully']);
    }
}
