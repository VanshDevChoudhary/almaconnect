<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(): Response
    {
        $events = Event::query()
            ->withCount(['rsvps as going_count' => fn ($q) => $q->where('status', 'going')])
            ->orderByDesc('starts_at')
            ->get()
            ->map(fn (Event $e) => [
                'slug' => $e->slug,
                'title' => $e->title,
                'starts_at' => $e->starts_at->toIso8601String(),
                'location' => $e->location,
                'going_count' => $e->going_count,
                'is_past' => $e->isPast(),
            ]);

        return Inertia::render('Admin/Events/Index', ['events' => $events]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Events/Create');
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        $event = Event::create(collect($data)->except('cover_image')->all());

        if ($request->hasFile('cover_image')) {
            $event->update(['cover_image' => $this->storeCover($request, $event)]);
        }

        return redirect()
            ->route('events.show', $event->slug)
            ->with('success', 'Event created.');
    }

    public function edit(string $slug): Response
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        return Inertia::render('Admin/Events/Edit', [
            'event' => [
                'slug' => $event->slug,
                'title' => $event->title,
                'description' => $event->description,
                'cover_image' => $event->cover_image,
                'starts_at' => $event->starts_at->format('Y-m-d\TH:i'),
                'ends_at' => $event->ends_at?->format('Y-m-d\TH:i'),
                'location' => $event->location,
                'online_url' => $event->online_url,
                'capacity' => $event->capacity,
            ],
        ]);
    }

    public function update(UpdateEventRequest $request, string $slug): RedirectResponse
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        $data = $request->validated();

        // Slug is intentionally NOT regenerated — keep the URL stable.
        $event->update(collect($data)->except('cover_image')->all());

        if ($request->hasFile('cover_image')) {
            if ($event->cover_image && Storage::disk('public')->exists($event->cover_image)) {
                Storage::disk('public')->delete($event->cover_image);
            }
            $event->update(['cover_image' => $this->storeCover($request, $event)]);
        }

        return redirect()
            ->route('events.show', $event->slug)
            ->with('success', 'Event updated.');
    }

    public function destroy(string $slug): RedirectResponse
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        if ($event->cover_image && Storage::disk('public')->exists($event->cover_image)) {
            Storage::disk('public')->delete($event->cover_image);
        }

        $event->delete(); // event_rsvps cascade via FK

        return redirect()
            ->route('admin.events.index')
            ->with('success', 'Event deleted.');
    }

    private function storeCover(Request $request, Event $event): string
    {
        $encoded = ImageManager::gd()
            ->read($request->file('cover_image')->getRealPath())
            ->scaleDown(width: 1920)
            ->toJpeg(85);

        $path = 'events/'.$event->id.'-'.now()->timestamp.'-'.Str::lower(Str::random(6)).'.jpg';
        Storage::disk('public')->put($path, (string) $encoded);

        return $path;
    }
}
