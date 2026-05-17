<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStoryRequest;
use App\Models\SuccessStory;
use App\Models\User;
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
    public function index(Request $request): Response
    {
        $status = $request->input('status');

        $query = SuccessStory::with('featuredUser', 'submitter')
            ->orderByRaw("FIELD(status, 'pending', 'published', 'rejected')")
            ->latest();

        if ($status && in_array($status, ['pending', 'published', 'rejected'], true)) {
            $query->where('status', $status);
        }

        $stories = $query->get()->map(fn (SuccessStory $s) => [
            'id' => $s->id,
            'slug' => $s->slug,
            'headline' => $s->headline,
            'category' => $s->category,
            'status' => $s->status,
            'featured' => $s->featuredUser?->name,
            'submitter' => $s->submitter?->name,
            'created_at' => $s->created_at->toIso8601String(),
        ]);

        return Inertia::render('Admin/Stories/Index', [
            'stories' => $stories,
            'filters' => ['status' => $status ?: 'all'],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Stories/Create', [
            'alumni' => User::where('role', 'alumni')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreStoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $admin = $request->user();

        $story = SuccessStory::create([
            'user_id' => $data['user_id'],
            'headline' => $data['headline'],
            'category' => $data['category'],
            'body' => Purifier::clean($data['body'], 'alumni_post'),
            'status' => $data['status'] ?? 'published',
            'submitted_by' => $admin->id,
            'reviewed_by' => $admin->id,
            'published_at' => ($data['status'] ?? 'published') === 'published' ? now() : null,
        ]);

        if ($request->hasFile('cover_image')) {
            $story->update(['cover_image' => $this->storeCover($request, $story)]);
        }

        return redirect()->route('admin.stories.index')->with('success', 'Story created.');
    }

    public function edit(SuccessStory $story): Response
    {
        return Inertia::render('Admin/Stories/Edit', [
            'story' => [
                'id' => $story->id,
                'headline' => $story->headline,
                'category' => $story->category,
                'body' => $story->body,
                'cover_image' => $story->cover_image,
                'status' => $story->status,
                'user_id' => $story->user_id,
            ],
            'alumni' => User::where('role', 'alumni')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(StoreStoryRequest $request, SuccessStory $story): RedirectResponse
    {
        $data = $request->validated();

        // Slug intentionally NOT regenerated — keep the URL stable.
        $story->update([
            'user_id' => $data['user_id'],
            'headline' => $data['headline'],
            'category' => $data['category'],
            'body' => Purifier::clean($data['body'], 'alumni_post'),
            'status' => $data['status'] ?? $story->status,
            'published_at' => ($data['status'] ?? $story->status) === 'published'
                ? ($story->published_at ?? now())
                : null,
        ]);

        if ($request->hasFile('cover_image')) {
            if ($story->cover_image && Storage::disk('public')->exists($story->cover_image)) {
                Storage::disk('public')->delete($story->cover_image);
            }
            $story->update(['cover_image' => $this->storeCover($request, $story)]);
        }

        return redirect()->route('admin.stories.index')->with('success', 'Story updated.');
    }

    public function destroy(SuccessStory $story): RedirectResponse
    {
        if ($story->cover_image && Storage::disk('public')->exists($story->cover_image)) {
            Storage::disk('public')->delete($story->cover_image);
        }
        $story->delete();

        return redirect()->route('admin.stories.index')->with('success', 'Story deleted.');
    }

    public function approve(Request $request, SuccessStory $story): RedirectResponse
    {
        $story->update([
            'status' => 'published',
            'reviewed_by' => $request->user()->id,
            'published_at' => $story->published_at ?? now(),
        ]);

        // TODO V2: email the submitter that their story was approved.

        return back()->with('success', 'Story published.');
    }

    public function reject(Request $request, SuccessStory $story): RedirectResponse
    {
        $story->update([
            'status' => 'rejected',
            'reviewed_by' => $request->user()->id,
        ]);

        // TODO V2: email the submitter that their story was rejected.

        return back()->with('success', 'Story rejected.');
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
