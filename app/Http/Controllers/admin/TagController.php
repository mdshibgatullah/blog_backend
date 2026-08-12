<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class TagController extends Controller
{
    public function index(){
        $tags = Cache::remember('admin.tags.index', 60, function () {
            return Tag::orderBy('created_at', 'DESC')->get();
        });
        return response()->json([
            'status' => 200,
            'data' => $tags
        ]);
    }


    // store tag
    public function store(Request $request){
            $validator = Validator::make($request->all(), [
                'name' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 400,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $tag = Tag::create([
                'name' => $request->name,
                'status' => $request->status,
            ]);

            Cache::forget('admin.tags.index');
        Cache::forget('front.tags.index');

            return response()->json([
                'status' => 200,
                'message' => 'Tag created successfully',
                'data' => $tag
            ]);

    }

    // show tag
    public function show($id){
        $tag = Tag::findOrFail($id);
       
        if($tag == null){
            return response()->json([
                'status' => 404,
                'message' => 'Tag not found',
                'data' => []
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data' => $tag
        ]);

    }


    // update tag
    public function update(Request $request, $id)
    {
        $tag = Tag::findOrFail($id);

        if (!$tag) {
            return response()->json([
                'status' => 404,
                'message' => 'Tag not found',
                'data' => []
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $tag->update([
            'name' => $request->name
        ]);

        Cache::forget('admin.tags.index');
        Cache::forget('front.tags.index');

        return response()->json([
            'status' => 200,
            'message' => 'Tag updated successfully',
            'data' => $tag
        ], 200);
    }


    // destroy tag
    public function destroy($id){
        $tag = Tag::findOrFail($id);
       
        if($tag == null){
            return response()->json([
                'status' => 404,
                'message' => 'Tag not found',
                'data' => []
            ], 404);
        }

        $tag->delete();

        Cache::forget('admin.tags.index');
        Cache::forget('front.tags.index');

        return response()->json([
            'status' => 200,
            'message' => 'Tag deleted successfully',
            'data' => $tag
        ], 200);
    }
}
