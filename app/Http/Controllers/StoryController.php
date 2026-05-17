<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStorySubmissionRequest;
use App\Models\SuccessStory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Inertia\Inertia;
use Inertia\Response;
use Mews\Purifier\Facades\Purifier;

class StoryController extends Controller
{
    public static function readTime(?string $body): int
    {
        $words = str_word_count(strip_tags((string) $body));

        return max(1, (int) ceil($words / 200));
    }

    private function card(SuccessStory $s): array
    {
        return [
            'slug' => $s->slug,
            'headline' => $s->headline,
            'category' => $s->category,
            'cover_image' => $s->cover_image,
            'read_time' => self::readTime($s->body),
            'published_at' => $s->published_at?->toIso8601String(),
            'author' => [
                'name' => $s->featuredUser?->name,
                'avatar' => $s->featuredUser?->avatar,
                'batch' => $s->featuredUser?->profile?->batch,
                'branch' => $s->featuredUser?->profile?->branch,
            ],
        ];
    }

    public function index(Request $request): Response
    {
        $category = $request->input('category');

        $query = SuccessStory::published()
            ->with('featuredUser.profile')
            ->latest('published_at');

        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }

        $stories = $query->get()->map(fn ($s) => $this->card($s));

        return Inertia::render('Stories/Index', [
            'featured' => $stories->first(),
            'stories' => $stories->slice(1)->values(),
            'category' => $category ?: 'all',
            'canSubmit' => in_array($request->user()->role, ['alumni', 'admin'], true),
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $story = SuccessStory::published()
            ->with('featuredUser.profile')
            ->where('slug', $slug)
            ->firstOrFail();

        $related = SuccessStory::published()
            ->where('id', '!=', $story->id)
            ->where('category', $story->category)
            ->with('featuredUser.profile')
            ->latest('published_at')
            ->limit(3)
            ->get();

        if ($related->count() < 3) {
            $more = SuccessStory::published()
                ->where('id', '!=', $story->id)
                ->whereNotIn('id', $related->pluck('id'))
                ->with('featuredUser.profile')
                ->latest('published_at')
                ->limit(3 - $related->count())
                ->get();
            $related = $related->concat($more);
        }

        return Inertia::render('Stories/Show', [
            'story' => [
                'slug' => $story->slug,
                'headline' => $story->headline,
                'category' => $story->category,
                'cover_image' => $story->cover_image,
                'body' => $story->body,
                'read_time' => self::readTime($story->body),
                'published_at' => $story->published_at?->toIso8601String(),
                'author' => [
                    'id' => $story->featuredUser?->id,
                    'name' => $story->featuredUser?->name,
                    'avatar' => $story->featuredUser?->avatar,
                    'company' => $story->featuredUser?->profile?->current_company,
                    'role' => $story->featuredUser?->profile?->current_role,
                    'city' => $story->featuredUser?->profile?->city,
                    'slug' => $story->featuredUser?->profile?->slug,
                ],
            ],
            'related' => $related->map(fn ($s) => $this->card($s)),
        ]);
    }

    public function createSubmission(): Response
    {
        return Inertia::render('Stories/Submit');
    }

    public function store(StoreStorySubmissionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = $request->user();

        $story = SuccessStory::create([
            'user_id' => $user->id,
            'headline' => $data['headline'],
            'category' => $data['category'],
            'body' => Purifier::clean($data['body'], 'alumni_post'),
            'status' => 'pending',
            'submitted_by' => $user->id,
        ]);

        $story->update(['cover_image' => $this->storeCover($request, $story)]);

        return back()->with('success', "Story submitted. We'll review it and let you know.");
    }

    public function mine(Request $request): Response
    {
        $stories = SuccessStory::where('submitted_by', $request->user()->id)
            ->latest()
            ->get()
            ->map(fn ($s) => [
                'slug' => $s->slug,
                'headline' => $s->headline,
                'category' => $s->category,
                'status' => $s->status,
                'created_at' => $s->created_at->toIso8601String(),
            ]);

        return Inertia::render('Stories/Mine', ['stories' => $stories]);
    }

    private function storeCover(Request $request, SuccessStory $story): string
    {
        $encoded = ImageManager::gd()
            ->read($request->file('cover_image')->getRealPath())
            ->scaleDown(width: 1920)
            ->toJpeg(85);

        $path = 'stories/'.$story->id.'-'.now()->timestamp.'-'.Str::lower(Str::random(6)).'.jpg';
        Storage::disk('public')->put($path, (string) $encoded);

        return $path;
    }
}
