<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;

class DashboardController extends Controller
{
    public function stats()
    {
        return response()->json([
            'status' => 200,
            'data' => [
                'total_posts'      => Post::count(),
                'total_categories' => Category::count(),
                'total_users'      => User::count(), 
                'total_views'      => (int) Post::sum('views'),
            ],
        ]);
    }
}
