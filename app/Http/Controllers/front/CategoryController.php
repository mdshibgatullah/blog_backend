<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Cache::remember('front.categories.index', 60, function () {
            return Category::where('status', 1)->orderBy('created_at', 'DESC')->get();
        });

        return response()->json([
            'status' => 200,
            'data' => $categories
        ]);
    }

    public function show($id)
    {
        $category = Category::where('status', 1)->find($id);

        if ($category == null) {
            return response()->json([
                'status'  => 404,
                'message' => 'Category not found',
                'data'    => []
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data'   => $category
        ]);
    }
}
