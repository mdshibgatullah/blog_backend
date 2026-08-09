<?php

use App\Http\Controllers\admin\AuthController;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\PostController;
use App\Http\Controllers\admin\TagController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/admin/login', [AuthController::class, 'login']);



Route::group(['middleware' => 'auth:sanctum'], function(){
    Route::resource('categories', CategoryController::class);
    Route::resource('tags', TagController::class);
    Route::resource('posts', PostController::class);
});
