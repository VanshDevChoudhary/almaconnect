<?php

use App\Models\Profile;
use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'alumni',
        'batch' => 2020,
        'branch' => 'CSE',
        'roll_no' => 'R12345',
        'terms' => true,
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    $user = User::where('email', 'test@example.com')->first();
    expect($user->role)->toBe('alumni');
    expect($user->status)->toBe('pending');
    expect(Profile::where('user_id', $user->id)->exists())->toBeTrue();
});

test('registration requires terms acceptance', function () {
    $response = $this->post('/register', [
        'name' => 'No Terms',
        'email' => 'noterms@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'alumni',
        'batch' => 2020,
        'branch' => 'CSE',
        'terms' => false,
    ]);

    $response->assertSessionHasErrors('terms');
    $this->assertGuest();
});

test('admin role cannot be set via public registration', function () {
    $this->post('/register', [
        'name' => 'Sneaky',
        'email' => 'sneaky@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'admin',
        'batch' => 2020,
        'branch' => 'CSE',
        'terms' => true,
    ])->assertSessionHasErrors('role');

    $this->assertGuest();
});
