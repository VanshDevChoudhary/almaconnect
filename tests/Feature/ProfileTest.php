<?php

use App\Models\User;

test('profile edit page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/profile/edit')->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch('/profile', [
        'name' => 'Updated Name',
        'batch' => 2019,
        'branch' => 'CSE',
        'bio' => 'Hello world.',
        'skills' => ['Python', 'Go'],
    ]);

    $response->assertSessionHasNoErrors()->assertRedirect(route('profile.edit'));

    $user->refresh();
    expect($user->name)->toBe('Updated Name');
    expect($user->profile->bio)->toBe('Hello world.');
    expect($user->profile->skills)->toBe(['Python', 'Go']);
});

test('email cannot be changed via profile update', function () {
    $user = User::factory()->create(['email' => 'keep@example.com']);

    $this->actingAs($user)->patch('/profile', [
        'name' => 'New Name',
        'email' => 'changed@example.com',
    ])->assertSessionHasNoErrors();

    expect($user->refresh()->email)->toBe('keep@example.com');
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->delete('/profile', [
        'password' => 'password',
    ])->assertRedirect('/');

    $this->assertGuest();
    expect($user->fresh())->toBeNull();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from('/profile')
        ->delete('/profile', ['password' => 'wrong-password'])
        ->assertSessionHasErrors('password');

    expect($user->fresh())->not->toBeNull();
});
