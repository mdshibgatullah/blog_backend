<?php

use App\Http\Controllers\admin\AuthController;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\TagController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/admin/login', [AuthController::class, 'login']);

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::group(['middleware' => 'auth:sanctum'], function(){
    // Route::get('/categories', [CategoryController::class, 'index']);
    // Route::post('/categories', [CategoryController::class, 'store']);
    // Route::get('/categories/{id}', [CategoryController::class, 'show']);
    // Route::put('/categories/{id}', [CategoryController::class, 'update']);
    // Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
    
    
    Route::resource('categories', CategoryController::class);
    Route::resource('tags', TagController::class);
});
