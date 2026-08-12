<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index(){
            $categories = Cache::remember('admin.categories.index', 60, function () {
                return Category::withCount('posts')->orderBy('created_at', 'DESC')->get();
            });
            return response()->json([
            'status' => 200,
            'data' => $categories
        ]);
    }


    // store category
    public function store(Request $request){
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'status' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 400,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $category = Category::create([
                'name' => $request->name,
                'status' => $request->status,
            ]);

            Cache::forget('admin.categories.index');
            Cache::forget('front.categories.index');

            return response()->json([
                'status' => 200,
                'message' => 'Category created successfully',
                'data' => $category
            ]);

    }

    // show category
    public function show($id){
        $category = Category::findOrFail($id);
       
        if($category == null){
            return response()->json([
                'status' => 404,
                'message' => 'Category not found',
                'data' => []
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data' => $category
        ]);

    }


    // update category
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        if (!$category) {
            return response()->json([
                'status' => 404,
                'message' => 'Category not found',
                'data' => []
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'status' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $category->update([
            'name' => $request->name,
            'status' => $request->status,
        ]);

        Cache::forget('admin.categories.index');
            Cache::forget('front.categories.index');

        return response()->json([
            'status' => 200,
            'message' => 'Category updated successfully',
            'data' => $category
        ], 200);
    }


    // destroy category
    public function destroy($id){
        $category = Category::findOrFail($id);
       
        if($category == null){
            return response()->json([
                'status' => 404,
                'message' => 'Category not found',
                'data' => []
            ], 404);
        }

        $category->delete();

        Cache::forget('admin.categories.index');
            Cache::forget('front.categories.index');

        return response()->json([
            'status' => 200,
            'message' => 'Category deleted successfully',
            'data' => $category
        ], 200);
    }
}
