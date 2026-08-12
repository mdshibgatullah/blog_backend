<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;

class PostController extends Controller
{
    // shob active (status=1) post — general listing (home, all-posts, category page)
    public function index()
    {
        return response()->json([
            'status' => 200,
            'data' => $this->activePosts()
        ]);
    }

    // shudhu popular flag thaka active post
    public function popular()
    {
        $posts = Cache::remember('front.posts.popular', 60, function () {
            return Post::with(['category', 'tags'])
                ->where('status', 1)
                ->where('popular', 1)
                ->orderBy('created_at', 'DESC')
                ->get();
        });

        return response()->json([
            'status' => 200,
            'data' => $posts
        ]);
    }

    // shudhu trending flag thaka active post
    public function trending()
    {
        $posts = Cache::remember('front.posts.trending', 60, function () {
            return Post::with(['category', 'tags'])
                ->where('status', 1)
                ->where('trending', 1)
                ->orderBy('created_at', 'DESC')
                ->get();
        });

        return response()->json([
            'status' => 200,
            'data' => $posts
        ]);
    }

    // single active post — draft (status=0) hole public visitor ke dekhano hobe na
    public function show($id)
    {
        $post = Post::with(['category', 'tags'])
            ->where('status', 1)
            ->find($id);

        if ($post == null) {
            return response()->json([
                'status'  => 404,
                'message' => 'Post not found',
                'data'    => []
            ], 404);
        }

        // post view count barano hocche — dashboard e "Total Views" ei theke ashe
        $post->increment('views');

        return response()->json([
            'status' => 200,
            'data'   => $post
        ]);
    }

    private function activePosts()
    {
        return Cache::remember('front.posts.index', 60, function () {
            return Post::with(['category', 'tags'])
                ->where('status', 1)
                ->orderBy('created_at', 'DESC')
                ->get();
        });
    }
}
