<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    // Fetch all posts
    public function index()
    {
        $posts = Post::with(['category', 'tags'])->orderBy('created_at', 'DESC')->get();

        return response()->json([
            'status' => 200,
            'data' => $posts
        ]);
    }

    // Store a new post
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors(),
            ], 422);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/posts'), $imageName);
            $imagePath = 'uploads/posts/' . $imageName;
        }

        $post = Post::create([
            'category_id' => $request->category_id,
            'title'       => $request->title,
            'image'       => $imagePath,
            'description' => $request->description,
            'popular'     => $request->popular,
            'status'      => $request->status
        ]);

        
        if ($request->has('tags') && is_array($request->tags)) {
            $post->tags()->attach($request->tags);
        }

        return response()->json([
            'status'  => 200,
            'message' => 'Post created successfully',
            'data'    => $post
        ]);
    }


    // Show single post
    public function show($id)
    {
        $post = Post::with(['category', 'tags'])->find($id);

        if ($post == null) {
            return response()->json([
                'status'  => 404,
                'message' => 'Post not found',
                'data'    => []
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data'   => $post
        ]);
    }

    // Update post
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        if (!$post) {
            return response()->json([
                'status'  => 404,
                'message' => 'Post not found',
                'data'    => []
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $imagePath = $post->image;
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($post->image && File::exists(public_path($post->image))) {
                File::delete(public_path($post->image));
            }

            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/posts'), $imageName);
            $imagePath = 'uploads/posts/' . $imageName;
        }

        $post->update([
            'category_id' => $request->category_id,
            'title'       => $request->title,
            'image'       => $imagePath,
            'description' => $request->description,
            'popular'     => $request->popular,
            'status'      => $request->status,
        ]);

        // Sync Tags
        if ($request->has('tags') && is_array($request->tags)) {
            $post->tags()->sync($request->tags);
        } else {
            $post->tags()->detach();
        }

        return response()->json([
            'status'  => 200,
            'message' => 'Post updated successfully',
            'data'    => $post->load(['category', 'tags'])
        ], 200);
    }


    // Destroy post
    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        if ($post == null) {
            return response()->json([
                'status'  => 404,
                'message' => 'Post not found',
                'data'    => []
            ], 404);
        }

        
        if ($post->image && File::exists(public_path($post->image))) {
            File::delete(public_path($post->image));
        }

        // Detach tags and delete post
        $post->tags()->detach();
        $post->delete();

        return response()->json([
            'status'  => 200,
            'message' => 'Post deleted successfully',
            'data'    => $post
        ], 200);
    }
}