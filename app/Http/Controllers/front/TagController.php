<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Support\Facades\Cache;

class TagController extends Controller
{
    public function index()
    {
        $tags = Cache::remember('front.tags.index', 60, function () {
            return Tag::orderBy('created_at', 'DESC')->get();
        });

        return response()->json([
            'status' => 200,
            'data' => $tags
        ]);
    }

    public function show($id)
    {
        $tag = Tag::find($id);

        if ($tag == null) {
            return response()->json([
                'status'  => 404,
                'message' => 'Tag not found',
                'data'    => []
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data'   => $tag
        ]);
    }
}
