<?php

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function approvedUserWithProfile(array $attrs = []): User
{
    $user = User::factory()->alumni()->create(array_merge(['email_verified_at' => now()], $attrs));
    Profile::factory()->create(['user_id' => $user->id]);

    return $user->fresh();
}

test('approved user can view a profile by slug', function () {
    $owner = approvedUserWithProfile();
    $viewer = approvedUserWithProfile();

    $this->actingAs($viewer)
        ->get(route('profile.show', $owner->profile->slug))
        ->assertOk();
});

test('non-existent slug returns 404', function () {
    $viewer = approvedUserWithProfile();

    $this->actingAs($viewer)->get('/profile/no-such-slug')->assertNotFound();
});

test('guest visiting a profile is redirected to login', function () {
    $owner = approvedUserWithProfile();

    $this->get(route('profile.show', $owner->profile->slug))
        ->assertRedirect(route('login'));
});

test('pending user is bounced from profile show but can reach profile edit', function () {
    $pending = User::factory()->create(['status' => 'pending', 'email_verified_at' => now()]);
    Profile::factory()->create(['user_id' => $pending->id]);
    $owner = approvedUserWithProfile();

    $this->actingAs($pending)
        ->get(route('profile.show', $owner->profile->slug))
        ->assertRedirect(route('pending-review'));

    $this->actingAs($pending)->get('/profile/edit')->assertOk();
});

test('owner sees isOwner true and others see false', function () {
    $owner = approvedUserWithProfile();
    $other = approvedUserWithProfile();

    $this->actingAs($owner)
        ->get(route('profile.show', $owner->profile->slug))
        ->assertInertia(fn ($page) => $page->where('isOwner', true));

    $this->actingAs($other)
        ->get(route('profile.show', $owner->profile->slug))
        ->assertInertia(fn ($page) => $page->where('isOwner', false));
});

test('slug stays stable when the name changes', function () {
    $user = approvedUserWithProfile();
    $originalSlug = $user->profile->slug;

    $this->actingAs($user)->patch('/profile', [
        'name' => 'Completely Different Name',
        'batch' => 2015,
    ])->assertSessionHasNoErrors();

    expect($user->profile->fresh()->slug)->toBe($originalSlug);
    expect($user->fresh()->name)->toBe('Completely Different Name');
});

test('oversized avatar is rejected', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('profile.avatar'), [
            'avatar' => UploadedFile::fake()->image('big.jpg')->size(6000),
        ])
        ->assertSessionHasErrors('avatar');
});

test('a spoofed non-image file is rejected by the mime re-check', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    // A plain file masquerading as a jpg.
    $fake = UploadedFile::fake()->create('malware.jpg', 50, 'image/jpeg');

    $this->actingAs($user)
        ->post(route('profile.avatar'), ['avatar' => $fake])
        ->assertSessionHasErrors('avatar');

    expect($user->fresh()->avatar)->toBeNull();
});

test('a valid avatar is processed, stored, and replaces the old one', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    // Seed an existing avatar to confirm it gets deleted.
    Storage::disk('public')->put('avatars/old.jpg', 'old');
    $user->update(['avatar' => 'avatars/old.jpg']);

    $response = $this->actingAs($user)->post(route('profile.avatar'), [
        'avatar' => UploadedFile::fake()->image('new.png', 800, 800),
    ]);

    $response->assertOk();
    $user->refresh();

    expect($user->avatar)->toStartWith('avatars/'.$user->id.'-');
    expect($user->avatar)->toEndWith('.jpg');
    Storage::disk('public')->assertExists($user->avatar);
    Storage::disk('public')->assertMissing('avatars/old.jpg');
});
