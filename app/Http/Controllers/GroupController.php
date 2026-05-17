<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GroupController extends Controller
{
    private const POSTS_PER_PAGE = 10;

    public function index(Request $request): Response
    {
        $user = $request->user();
        $type = $request->input('type');
        $search = trim((string) $request->input('q', ''));

        $query = Group::query()->withCount('members');

        if (! $user->isAdmin()) {
            $query->where('is_public', true);
        }
        if ($type && $type !== 'all') {
            $query->where('type', $type);
        }
        if ($search !== '') {
            $query->where('name', 'like', '%'.$search.'%');
        }

        $myGroupIds = $user->groups()->pluck('groups.id')->all();

        $groups = $query->latest()->get()->map(fn (Group $g) => [
            'slug' => $g->slug,
            'name' => $g->name,
            'description' => $g->description,
            'cover_image' => $g->cover_image,
            'type' => $g->type,
            'members_count' => $g->members_count,
            'is_member' => in_array($g->id, $myGroupIds, true),
        ]);

        return Inertia::render('Groups/Index', [
            'groups' => $groups,
            'filters' => ['type' => $type ?: 'all', 'q' => $search],
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $user = $request->user();
        $group = Group::where('slug', $slug)->withCount('members')->firstOrFail();

        $isMember = $user->isMemberOf($group->id);
        $isModerator = $user->isModeratorOf($group->id);
        $isCreator = $group->created_by === $user->id;

        $paginator = Post::where('group_id', $group->id)
            ->with(['user', 'comments.user'])
            ->withCount(['likes', 'comments'])
            ->withExists(['likes as liked' => fn ($q) => $q->where('user_id', $user->id)])
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->paginate(self::POSTS_PER_PAGE);

        $paginator->through(fn (Post $p) => [
            'id' => $p->id,
            'body' => $p->body,
            'image' => $p->image,
            'is_pinned' => $p->is_pinned,
            'created_at' => $p->created_at->toIso8601String(),
            'likes_count' => $p->likes_count,
            'comments_count' => $p->comments_count,
            'liked' => (bool) $p->liked,
            'can_delete' => $user->can('delete', $p),
            'can_pin' => $user->can('pin', $p),
            'author' => [
                'id' => $p->user?->id,
                'name' => $p->user?->name,
                'avatar' => $p->user?->avatar,
            ],
            'comments' => $p->comments->sortBy('created_at')->values()->map(fn ($c) => [
                'id' => $c->id,
                'body' => $c->body,
                'created_at' => $c->created_at->toIso8601String(),
                'can_delete' => $user->can('delete', $c),
                'author' => [
                    'id' => $c->user?->id,
                    'name' => $c->user?->name,
                    'avatar' => $c->user?->avatar,
                ],
            ]),
        ]);

        $members = $group->members()
            ->orderByPivot('joined_at', 'desc')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'avatar' => $m->avatar,
                'role' => $m->pivot->role,
                'joined_at' => $m->pivot->joined_at,
            ]);

        return Inertia::render('Groups/Show', [
            'group' => [
                'slug' => $group->slug,
                'name' => $group->name,
                'description' => $group->description,
                'cover_image' => $group->cover_image,
                'type' => $group->type,
                'members_count' => $group->members_count,
            ],
            'posts' => $paginator,
            'members' => $members,
            'isMember' => $isMember,
            'isModerator' => $isModerator,
            'isCreator' => $isCreator,
        ]);
    }

    public function join(Request $request, string $slug): RedirectResponse
    {
        $user = $request->user();
        $group = Group::where('slug', $slug)->firstOrFail();

        if (! $user->isMemberOf($group->id)) {
            $group->members()->attach($user->id, [
                'role' => 'member',
                'joined_at' => now(),
            ]);
        }

        return back();
    }

    public function leave(Request $request, string $slug): RedirectResponse
    {
        $user = $request->user();
        $group = Group::where('slug', $slug)->firstOrFail();

        // V1: the creator cannot leave their own group.
        if ($group->created_by === $user->id) {
            return back()->with('error', 'Group creators cannot leave their own group.');
        }

        $group->members()->detach($user->id);

        return back();
    }
}
