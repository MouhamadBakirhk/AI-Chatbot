<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\ChatMessageController;

// Public routes
Route::post('/register', [UserController::class,'register']);
Route::post('/login', [UserController::class,'login']);
Route::get('/products', [ProductController::class,'index']);
Route::get('/products/{id}', [ProductController::class,'show']);
Route::get('/products/{id}/ratings', [RatingController::class, 'show']);

// ChatMessage API
Route::get('/chat', [ChatMessageController::class, 'index']); 
Route::post('/chat', [ChatMessageController::class, 'store']);
Route::post('/chatbot', [ChatMessageController::class, 'reply']);
// Authenticated routes
Route::middleware(['jwt.auth'])->group(function () {
    Route::get('/me', [UserController::class,'me']);
    Route::post('/cart', [CartController::class,'store']);
    Route::get('/cart', [CartController::class,'index']);
    Route::put('/cart/{id}', [CartController::class,'update']);
    Route::delete('/cart/{id}', [CartController::class,'destroy']);
    Route::post('/checkout', [OrderController::class,'checkout']);
    Route::get('/orders', [OrderController::class,'index']);  
    Route::get('/orders/{id}', [OrderController::class,'show']);
    Route::post('/ratings', [RatingController::class, 'store']);
}); 

Route::put('/products/{id}', [ProductController::class,'update']);

// Admin-only routes
Route::middleware(['jwt.auth','isAdmin'])->group(function () {
    Route::post('/products', [ProductController::class,'store']);
    Route::delete('/products/{id}', [ProductController::class,'destroy']);
});
