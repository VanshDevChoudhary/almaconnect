<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, Post $post): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->isMemberOf($post->group_id), 403, 'Join the group to comment.');

        Comment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            // Plain text only — strip any HTML before storing.
            'body' => strip_tags($request->input('body')),
        ]);

        return back()->with('success', 'Comment added.');
    }

    public function destroy(Request $request, Comment $comment): RedirectResponse
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return back()->with('success', 'Comment deleted.');
    }
}
