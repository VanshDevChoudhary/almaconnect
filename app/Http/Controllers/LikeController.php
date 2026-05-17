<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function toggle(Request $request, Post $post): JsonResponse
    {
        $user = $request->user();

        abort_unless($user->isMemberOf($post->group_id), 403, 'Join the group to react.');

        $existing = Like::where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            // Unique (post_id, user_id) guards against double-likes at the
            // DB level; firstOrCreate avoids a race throwing.
            Like::firstOrCreate(['post_id' => $post->id, 'user_id' => $user->id]);
            $liked = true;
        }

        return response()->json([
            'liked' => $liked,
            'likes_count' => $post->likes()->count(),
        ]);
    }
}
