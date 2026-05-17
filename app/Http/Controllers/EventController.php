<?php

namespace App\Http\Controllers;

use App\Http\Requests\RSVPRequest;
use App\Models\Event;
use App\Models\EventRsvp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(Request $request): Response
    {
        $uid = $request->user()->id;

        $countSelects = [
            'rsvps as going_count' => fn ($q) => $q->where('status', 'going'),
            'rsvps as interested_count' => fn ($q) => $q->where('status', 'interested'),
        ];

        $map = fn (Event $e) => [
            'slug' => $e->slug,
            'title' => $e->title,
            'description' => $e->description,
            'cover_image' => $e->cover_image,
            'starts_at' => $e->starts_at->toIso8601String(),
            'ends_at' => $e->ends_at?->toIso8601String(),
            'location' => $e->location,
            'online_url' => $e->online_url,
            'capacity' => $e->capacity,
            'going_count' => $e->going_count,
            'interested_count' => $e->interested_count,
            'user_status' => $e->rsvps->first()?->status,
        ];

        $upcoming = Event::query()
            ->withCount($countSelects)
            ->with(['rsvps' => fn ($q) => $q->where('user_id', $uid)])
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->get()
            ->map($map);

        $past = Event::query()
            ->withCount($countSelects)
            ->with(['rsvps' => fn ($q) => $q->where('user_id', $uid)])
            ->where('starts_at', '<', now())
            ->orderByDesc('starts_at')
            ->get()
            ->map($map);

        return Inertia::render('Events/Index', [
            'upcoming' => $upcoming,
            'past' => $past,
            'tab' => $request->input('tab', 'upcoming'),
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $user = $request->user();
        $event = Event::where('slug', $slug)
            ->withCount([
                'rsvps as going_count' => fn ($q) => $q->where('status', 'going'),
                'rsvps as interested_count' => fn ($q) => $q->where('status', 'interested'),
            ])
            ->firstOrFail();

        $userStatus = EventRsvp::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->value('status');

        $attendees = $event->attendees()
            ->take(12)
            ->get()
            ->map(fn ($a) => ['id' => $a->id, 'name' => $a->name, 'avatar' => $a->avatar]);

        return Inertia::render('Events/Show', [
            'event' => [
                'slug' => $event->slug,
                'title' => $event->title,
                'description' => $event->description,
                'cover_image' => $event->cover_image,
                'starts_at' => $event->starts_at->toIso8601String(),
                'ends_at' => $event->ends_at?->toIso8601String(),
                'location' => $event->location,
                'online_url' => $event->online_url,
                'capacity' => $event->capacity,
                'going_count' => $event->going_count,
                'interested_count' => $event->interested_count,
                'is_past' => $event->isPast(),
            ],
            'userStatus' => $userStatus,
            'attendees' => $attendees,
            'attendeesTotal' => $event->going_count,
            'isAdmin' => $user->isAdmin(),
        ]);
    }

    public function rsvp(RSVPRequest $request, string $slug): JsonResponse
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        $user = $request->user();
        $status = $request->validated()['status'];

        if ($event->isPast()) {
            abort(422, 'This event has already taken place.');
        }

        DB::transaction(function () use ($event, $user, $status) {
            $locked = Event::lockForUpdate()->find($event->id);

            if ($status === 'going' && $locked->capacity !== null) {
                $going = EventRsvp::where('event_id', $locked->id)
                    ->where('status', 'going')
                    ->count();
                $alreadyGoing = EventRsvp::where('event_id', $locked->id)
                    ->where('user_id', $user->id)
                    ->where('status', 'going')
                    ->exists();

                if ($going >= $locked->capacity && ! $alreadyGoing) {
                    abort(422, 'Event is full');
                }
            }

            // event_rsvps has a composite (event_id, user_id) primary key and
            // no `id`, so Eloquent's updateOrCreate update path breaks. Do an
            // explicit insert-or-update keyed on the real columns.
            $exists = EventRsvp::where('event_id', $locked->id)
                ->where('user_id', $user->id)
                ->exists();

            if ($exists) {
                EventRsvp::where('event_id', $locked->id)
                    ->where('user_id', $user->id)
                    ->update(['status' => $status, 'updated_at' => now()]);
            } else {
                EventRsvp::create([
                    'event_id' => $locked->id,
                    'user_id' => $user->id,
                    'status' => $status,
                ]);
            }
        });

        return response()->json([
            'user_status' => $status,
            'going_count' => $event->goingCount(),
            'interested_count' => $event->interestedCount(),
        ]);
    }
}
