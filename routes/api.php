
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;

Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders', [OrderController::class, 'index']);
Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);
// مسارات المنتجات
Route::get('/products', [ProductController::class, 'index']);
Route::post('/products', [ProductController::class, 'store']);
Route::put('/products/{product}', [ProductController::class, 'update']);
Route::delete('/products/{product}', [ProductController::class, 'destroy']);