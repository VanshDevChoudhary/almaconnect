<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Models\Group;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Mews\Purifier\Facades\Purifier;

class PostController extends Controller
{
    public function store(StorePostRequest $request, string $slug): RedirectResponse
    {
        $user = $request->user();
        $group = Group::where('slug', $slug)->firstOrFail();

        abort_unless($user->isMemberOf($group->id), 403, 'Join the group to post.');

        $body = Purifier::clean($request->input('body'), 'alumni_post');

        DB::transaction(function () use ($request, $group, $user, $body) {
            $post = Post::create([
                'group_id' => $group->id,
                'user_id' => $user->id,
                'body' => $body,
                'is_pinned' => false,
            ]);

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $encoded = ImageManager::gd()
                    ->read($file->getRealPath())
                    ->scaleDown(width: 1200)
                    ->toJpeg(85);

                $path = 'posts/'.$post->id.'-'.now()->timestamp.'-'.Str::lower(Str::random(6)).'.jpg';
                Storage::disk('public')->put($path, (string) $encoded);
                $post->update(['image' => $path]);
            }
        });

        return back()->with('success', 'Posted.');
    }

    public function destroy(Request $request, Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        if ($post->image && Storage::disk('public')->exists($post->image)) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();

        return back()->with('success', 'Deleted.');
    }

    public function pin(Request $request, Post $post): RedirectResponse
    {
        $this->authorize('pin', $post);
        $post->update(['is_pinned' => true]);

        return back();
    }

    public function unpin(Request $request, Post $post): RedirectResponse
    {
        $this->authorize('pin', $post);
        $post->update(['is_pinned' => false]);

        return back();
    }
}
