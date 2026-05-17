<?php

use App\Models\Profile;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery\MockInterface;

test('alumni registering with public email is pending and bounced to pending-review', function () {
    $this->post('/register', [
        'name' => 'Test Alumni',
        'email' => 'testalumni@gmail.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'alumni',
        'batch' => 2020,
        'branch' => 'CSE',
        'terms' => true,
    ]);

    $user = User::where('email', 'testalumni@gmail.com')->first();
    expect($user->status)->toBe('pending');

    $user->forceFill(['email_verified_at' => now()])->save();

    $this->actingAs($user)->get('/dashboard')
        ->assertRedirect(route('pending-review'));
});

test('student with institute domain is auto-approved and reaches dashboard', function () {
    $this->post('/register', [
        'name' => 'Test Student',
        'email' => 'student@institute.edu',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'student',
        'batch' => 2025,
        'branch' => 'ECE',
        'terms' => true,
    ]);

    $user = User::where('email', 'student@institute.edu')->first();
    expect($user->status)->toBe('approved');

    $user->forceFill(['email_verified_at' => now()])->save();

    $this->actingAs($user)->get('/dashboard')->assertOk();
});

test('student with non-institute domain stays pending', function () {
    $this->post('/register', [
        'name' => 'Test Student 2',
        'email' => 'student2@gmail.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'student',
        'batch' => 2025,
        'branch' => 'ECE',
        'terms' => true,
    ]);

    expect(User::where('email', 'student2@gmail.com')->first()->status)->toBe('pending');
});

test('approved alumni reaches the dashboard', function () {
    $user = User::factory()->alumni()->create(['email_verified_at' => now()]);

    $this->actingAs($user)->get('/dashboard')->assertOk();
});

test('banned user is sent to access-denied with banned reason', function () {
    $user = User::factory()->create(['status' => 'banned', 'email_verified_at' => now()]);

    $this->actingAs($user)->get('/dashboard')
        ->assertRedirect(route('access-denied', ['reason' => 'banned']));
});

test('rejected user is sent to access-denied with rejected reason', function () {
    $user = User::factory()->create(['status' => 'rejected', 'email_verified_at' => now()]);

    $this->actingAs($user)->get('/dashboard')
        ->assertRedirect(route('access-denied', ['reason' => 'rejected']));
});

test('admin reaches dashboard and admin page', function () {
    $admin = User::factory()->admin()->create(['email_verified_at' => now()]);

    $this->actingAs($admin)->get('/dashboard')->assertOk();
    $this->actingAs($admin)->get('/admin')->assertOk();
});

test('non-admin is forbidden from the admin page', function () {
    $user = User::factory()->alumni()->create(['email_verified_at' => now()]);

    $this->actingAs($user)->get('/admin')->assertForbidden();
});

test('pending user can log out from pending-review', function () {
    $user = User::factory()->create(['status' => 'pending', 'email_verified_at' => now()]);

    $this->actingAs($user)->post('/logout')->assertRedirect('/');
    $this->assertGuest();
});

test('google callback creates a new pending alumni with profile', function () {
    $abstract = (new SocialiteUser)->map([
        'id' => 'google-123',
        'name' => 'Google Person',
        'email' => 'googleperson@gmail.com',
    ]);

    $this->mock('Laravel\Socialite\Contracts\Factory', function (MockInterface $mock) use ($abstract) {
        $provider = Mockery::mock();
        $provider->shouldReceive('user')->andReturn($abstract);
        $mock->shouldReceive('driver')->with('google')->andReturn($provider);
    });

    $this->get('/auth/google/callback')->assertRedirect(route('dashboard', absolute: false));

    $user = User::where('email', 'googleperson@gmail.com')->first();
    expect($user)->not->toBeNull();
    expect($user->google_id)->toBe('google-123');
    expect($user->role)->toBe('alumni');
    expect($user->status)->toBe('pending');
    expect($user->email_verified_at)->not->toBeNull();
    expect(Profile::where('user_id', $user->id)->exists())->toBeTrue();
    $this->assertAuthenticatedAs($user);
});

test('google callback links google_id to an existing email account', function () {
    $existing = User::factory()->create([
        'email' => 'existing@gmail.com',
        'google_id' => null,
    ]);

    $abstract = (new SocialiteUser)->map([
        'id' => 'google-999',
        'name' => 'Existing Person',
        'email' => 'existing@gmail.com',
    ]);

    $this->mock('Laravel\Socialite\Contracts\Factory', function (MockInterface $mock) use ($abstract) {
        $provider = Mockery::mock();
        $provider->shouldReceive('user')->andReturn($abstract);
        $mock->shouldReceive('driver')->with('google')->andReturn($provider);
    });

    $this->get('/auth/google/callback');

    expect(User::where('email', 'existing@gmail.com')->count())->toBe(1);
    expect($existing->fresh()->google_id)->toBe('google-999');
    $this->assertAuthenticatedAs($existing->fresh());
});
