<?php

use App\Jobs\SendEventReminders;
use App\Mail\EventReminderMail;
use App\Models\Event;
use App\Models\EventRsvp;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

function approvedMember(array $attrs = []): User
{
    return User::factory()->alumni()->create(array_merge(['email_verified_at' => now()], $attrs));
}

function adminUser(): User
{
    return User::factory()->admin()->create(['email_verified_at' => now()]);
}

test('events index shows upcoming and past for approved users only', function () {
    $admin = adminUser();
    Event::factory(2)->upcoming()->create(['created_by' => $admin->id]);
    Event::factory(1)->past()->create(['created_by' => $admin->id]);

    $this->get(route('events.index'))->assertRedirect(route('login'));

    $pending = User::factory()->create(['status' => 'pending', 'email_verified_at' => now()]);
    $this->actingAs($pending)->get(route('events.index'))->assertRedirect(route('pending-review'));

    $this->actingAs(approvedMember())
        ->get(route('events.index'))
        ->assertInertia(fn ($p) => $p->component('Events/Index')
            ->has('upcoming', 2)
            ->has('past', 1));
});

test('rsvp going then switching adjusts counts', function () {
    $admin = adminUser();
    $user = approvedMember();
    $event = Event::factory()->upcoming()->create(['created_by' => $admin->id, 'capacity' => null]);

    $this->actingAs($user)
        ->postJson(route('events.rsvp', $event->slug), ['status' => 'going'])
        ->assertOk()
        ->assertJson(['user_status' => 'going', 'going_count' => 1, 'interested_count' => 0]);

    $this->actingAs($user)
        ->postJson(route('events.rsvp', $event->slug), ['status' => 'interested'])
        ->assertOk()
        ->assertJson(['user_status' => 'interested', 'going_count' => 0, 'interested_count' => 1]);

    // Idempotent
    $this->actingAs($user)
        ->postJson(route('events.rsvp', $event->slug), ['status' => 'interested'])
        ->assertOk()
        ->assertJson(['going_count' => 0, 'interested_count' => 1]);

    expect(EventRsvp::where('event_id', $event->id)->count())->toBe(1);
});

test('capacity is enforced for going only and frees up on change', function () {
    $admin = adminUser();
    $a = approvedMember();
    $b = approvedMember();
    $event = Event::factory()->upcoming()->create(['created_by' => $admin->id, 'capacity' => 1]);

    $this->actingAs($a)
        ->postJson(route('events.rsvp', $event->slug), ['status' => 'going'])
        ->assertOk();

    // B is blocked from going...
    $this->actingAs($b)
        ->postJson(route('events.rsvp', $event->slug), ['status' => 'going'])
        ->assertStatus(422);

    // ...but can still mark interested.
    $this->actingAs($b)
        ->postJson(route('events.rsvp', $event->slug), ['status' => 'interested'])
        ->assertOk();

    // A frees the spot.
    $this->actingAs($a)
        ->postJson(route('events.rsvp', $event->slug), ['status' => 'not_going'])
        ->assertOk();

    $this->actingAs($b)
        ->postJson(route('events.rsvp', $event->slug), ['status' => 'going'])
        ->assertOk()
        ->assertJson(['going_count' => 1]);
});

test('rsvp to a past event is rejected', function () {
    $admin = adminUser();
    $user = approvedMember();
    $event = Event::factory()->past()->create(['created_by' => $admin->id]);

    $this->actingAs($user)
        ->postJson(route('events.rsvp', $event->slug), ['status' => 'going'])
        ->assertStatus(422);
});

test('non-admins cannot reach admin event routes', function () {
    $user = approvedMember();

    $this->actingAs($user)->get(route('admin.events.index'))->assertForbidden();
    $this->actingAs($user)->get(route('admin.events.create'))->assertForbidden();
    $this->actingAs($user)->post(route('admin.events.store'), [])->assertForbidden();
});

test('admin can create an event with an auto slug containing the year', function () {
    $admin = adminUser();
    $start = now()->addDays(14);

    $this->actingAs($admin)->post(route('admin.events.store'), [
        'title' => 'Annual Meet',
        'description' => 'Come along **everyone**.',
        'starts_at' => $start->format('Y-m-d\TH:i'),
        'capacity' => 50,
    ])->assertRedirect();

    $event = Event::first();
    expect($event)->not->toBeNull();
    expect($event->slug)->toBe('annual-meet-'.$start->year);
});

test('event creation validates dates', function () {
    $admin = adminUser();

    $this->actingAs($admin)->post(route('admin.events.store'), [
        'title' => 'Past Event',
        'description' => 'x',
        'starts_at' => now()->subDay()->format('Y-m-d\TH:i'),
    ])->assertSessionHasErrors('starts_at');

    $start = now()->addDays(5);
    $this->actingAs($admin)->post(route('admin.events.store'), [
        'title' => 'Bad Range',
        'description' => 'x',
        'starts_at' => $start->format('Y-m-d\TH:i'),
        'ends_at' => $start->copy()->subHour()->format('Y-m-d\TH:i'),
    ])->assertSessionHasErrors('ends_at');
});

test('admin can edit an event and the slug stays stable', function () {
    $admin = adminUser();
    $event = Event::factory()->upcoming()->create(['created_by' => $admin->id, 'title' => 'Old Title']);
    $originalSlug = $event->slug;

    $this->actingAs($admin)->patch(route('admin.events.update', $event->slug), [
        'title' => 'Brand New Title',
        'description' => $event->description,
        'starts_at' => $event->starts_at->format('Y-m-d\TH:i'),
    ])->assertRedirect();

    $event->refresh();
    expect($event->title)->toBe('Brand New Title');
    expect($event->slug)->toBe($originalSlug);
});

test('admin delete cascades rsvps', function () {
    $admin = adminUser();
    $user = approvedMember();
    $event = Event::factory()->upcoming()->create(['created_by' => $admin->id]);
    EventRsvp::create(['event_id' => $event->id, 'user_id' => $user->id, 'status' => 'going']);

    $this->actingAs($admin)
        ->delete(route('admin.events.destroy', $event->slug))
        ->assertRedirect(route('admin.events.index'));

    expect(Event::find($event->id))->toBeNull();
    expect(EventRsvp::where('event_id', $event->id)->count())->toBe(0);
});

test('send event reminders job queues mail for going attendees ~24h out', function () {
    Mail::fake();
    $admin = adminUser();
    $going = approvedMember();
    $interested = approvedMember();

    $event = Event::factory()->create([
        'created_by' => $admin->id,
        'starts_at' => now()->addHours(24)->addMinutes(30),
    ]);
    EventRsvp::create(['event_id' => $event->id, 'user_id' => $going->id, 'status' => 'going']);
    EventRsvp::create(['event_id' => $event->id, 'user_id' => $interested->id, 'status' => 'interested']);

    (new SendEventReminders)->handle();

    Mail::assertQueued(EventReminderMail::class, 1);
    Mail::assertQueued(EventReminderMail::class, fn ($m) => $m->hasTo($going->email));
});
