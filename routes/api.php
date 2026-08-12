<?php

use App\Http\Controllers\admin\AuthController;
use App\Http\Controllers\admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\admin\PostController as AdminPostController;
use App\Http\Controllers\admin\TagController as AdminTagController;
use App\Http\Controllers\front\CategoryController as FrontCategoryController;
use App\Http\Controllers\front\PostController as FrontPostController;
use App\Http\Controllers\front\TagController as FrontTagController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public (UI) routes — login lagbe na
| Home, sidebar, category page, single post page — shob ei route gulo
| use kore. Shudhu status=1 (active) data e ashbe.
|--------------------------------------------------------------------------
*/
Route::get('/posts', [FrontPostController::class, 'index']);
Route::get('/posts/popular', [FrontPostController::class, 'popular']);   // /posts/{id} er age thakte hobe
Route::get('/posts/trending', [FrontPostController::class, 'trending']); // /posts/{id} er age thakte hobe
Route::get('/posts/{post}', [FrontPostController::class, 'show']);

Route::get('/categories', [FrontCategoryController::class, 'index']);
Route::get('/categories/{category}', [FrontCategoryController::class, 'show']);

Route::get('/tags', [FrontTagController::class, 'index']);
Route::get('/tags/{tag}', [FrontTagController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Admin auth — login, forgot/reset password, email verification.
| Egulo public (kono token chara hit kora jay), karon user tokhono
| login e nei.
|--------------------------------------------------------------------------
*/
Route::post('/admin/login', [AuthController::class, 'login']);
Route::post('/admin/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/admin/reset-password', [AuthController::class, 'resetPassword']);

// Email er signed link theke ashe, tai 'signed' middleware diye URL tamper-proof kora
Route::get('/admin/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware('signed')
    ->name('verification.verify');

/*
|--------------------------------------------------------------------------
| Admin routes — sudhu logged-in admin/sub-admin access korte parbe.
| Ei route gulo shob (draft soho) data dey, admin panel er jonno.
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'admin'], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/email/verification-notification', [AuthController::class, 'sendVerificationEmail']);

    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    Route::get('/posts', [AdminPostController::class, 'index']);
    Route::get('/posts/{post}', [AdminPostController::class, 'show']);
    Route::post('/posts', [AdminPostController::class, 'store']);
    Route::put('/posts/{post}', [AdminPostController::class, 'update']);
    Route::patch('/posts/{post}', [AdminPostController::class, 'update']);
    Route::delete('/posts/{post}', [AdminPostController::class, 'destroy']);

    Route::get('/categories', [AdminCategoryController::class, 'index']);
    Route::get('/categories/{category}', [AdminCategoryController::class, 'show']);
    Route::post('/categories', [AdminCategoryController::class, 'store']);
    Route::put('/categories/{category}', [AdminCategoryController::class, 'update']);
    Route::patch('/categories/{category}', [AdminCategoryController::class, 'update']);
    Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy']);

    Route::get('/tags', [AdminTagController::class, 'index']);
    Route::get('/tags/{tag}', [AdminTagController::class, 'show']);
    Route::post('/tags', [AdminTagController::class, 'store']);
    Route::put('/tags/{tag}', [AdminTagController::class, 'update']);
    Route::patch('/tags/{tag}', [AdminTagController::class, 'update']);
    Route::delete('/tags/{tag}', [AdminTagController::class, 'destroy']);

    // Sub-admin management — shudhu super admin (role=admin) er jonno
    Route::group(['middleware' => 'super_admin'], function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::get('/sub-admins', [AuthController::class, 'subAdmins']);
        Route::delete('/sub-admins/{id}', [AuthController::class, 'destroySubAdmin']);
    });
});
