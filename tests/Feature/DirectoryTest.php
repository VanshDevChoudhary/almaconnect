<?php

use App\Models\Profile;
use App\Models\User;

function approvedAlumnus(array $profile = []): User
{
    $user = User::factory()->alumni()->create(['email_verified_at' => now()]);
    Profile::factory()->create(array_merge(['user_id' => $user->id], $profile));

    return $user->fresh();
}

test('directory requires an approved authenticated user', function () {
    $owner = approvedAlumnus();

    // Guest
    $this->get(route('directory'))->assertRedirect(route('login'));

    // Pending user
    $pending = User::factory()->create(['status' => 'pending', 'email_verified_at' => now()]);
    Profile::factory()->create(['user_id' => $pending->id]);
    $this->actingAs($pending)->get(route('directory'))->assertRedirect(route('pending-review'));

    // Approved user
    $this->actingAs($owner)->get(route('directory'))->assertOk();
});

test('directory lists only approved profiles and paginates at 24', function () {
    collect(range(1, 30))->each(fn () => approvedAlumnus());

    // A pending and a banned profile that must NOT appear.
    $pending = User::factory()->create(['status' => 'pending', 'email_verified_at' => now()]);
    Profile::factory()->create(['user_id' => $pending->id, 'current_company' => 'HiddenCo']);
    $banned = User::factory()->create(['status' => 'banned', 'email_verified_at' => now()]);
    Profile::factory()->create(['user_id' => $banned->id, 'current_company' => 'HiddenCo']);

    $viewer = approvedAlumnus();

    $this->actingAs($viewer)
        ->get(route('directory'))
        ->assertInertia(fn ($page) => $page
            ->component('Directory/Index')
            ->where('total', 31) // 30 + viewer
            ->where('alumni.per_page', 24)
            ->count('alumni.data', 24)
        );
});

test('search narrows results by company', function () {
    $viewer = approvedAlumnus(['current_company' => 'Acme']);
    approvedAlumnus(['current_company' => 'Acme']);
    $googler = approvedAlumnus(['current_company' => 'Google']);

    $this->actingAs($viewer)
        ->get(route('directory', ['q' => 'Google']))
        ->assertInertia(fn ($page) => $page
            ->where('total', 1)
            ->where('alumni.data.0.slug', $googler->profile->slug)
        );
});

test('batch filter narrows results', function () {
    $viewer = approvedAlumnus(['batch' => 2015]);
    approvedAlumnus(['batch' => 2020]);
    approvedAlumnus(['batch' => 2020]);

    $this->actingAs($viewer)
        ->get(route('directory', ['batch' => [2020]]))
        ->assertInertia(fn ($page) => $page->where('total', 2));
});

test('search and filter combine as an intersection', function () {
    $viewer = approvedAlumnus(['batch' => 2010, 'current_company' => 'Acme']);
    approvedAlumnus(['batch' => 2020, 'current_company' => 'Google']);
    $target = approvedAlumnus(['batch' => 2021, 'current_company' => 'Google']);

    $this->actingAs($viewer)
        ->get(route('directory', ['q' => 'Google', 'batch' => [2021]]))
        ->assertInertia(fn ($page) => $page
            ->where('total', 1)
            ->where('alumni.data.0.slug', $target->profile->slug)
        );
});

test('gibberish search yields an empty result set', function () {
    $viewer = approvedAlumnus(['current_company' => 'Acme']);

    $this->actingAs($viewer)
        ->get(route('directory', ['q' => 'zzxqweqweasdkjh']))
        ->assertInertia(fn ($page) => $page->where('total', 0)->count('alumni.data', 0));
});

test('pagination preserves the query string', function () {
    // 26 + viewer = 27 matching "Acme": page 1 = 24, page 2 = 3.
    collect(range(1, 26))->each(fn () => approvedAlumnus(['current_company' => 'Acme']));
    $viewer = approvedAlumnus(['current_company' => 'Acme']);

    $this->actingAs($viewer)
        ->get(route('directory', ['q' => 'Acme', 'page' => 2]))
        ->assertInertia(fn ($page) => $page
            ->where('alumni.current_page', 2)
            ->where('searchQuery', 'Acme')
            ->where('total', 27)
            ->count('alumni.data', 3)
        );
});
