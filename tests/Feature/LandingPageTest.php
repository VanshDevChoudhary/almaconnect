<?php

use App\Models\SuccessStory;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

test('guest visiting / sees the landing page as a Blade view', function () {
    $resp = $this->get('/');

    $resp->assertOk()
        ->assertViewIs('welcome')
        ->assertSee('AlmaConnect')
        ->assertSee('Get started');
});

test('authenticated approved user visiting / is redirected to /dashboard', function () {
    $user = User::factory()->alumni()->create([
        'email_verified_at' => now(),
        'status' => 'approved',
    ]);

    $this->actingAs($user)->get('/')->assertRedirect('/dashboard');
});

test('landing page passes stats and stories to the view', function () {
    $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
    SuccessStory::factory(2)->published()->create(['user_id' => $admin->id]);

    $resp = $this->get('/');
    $resp->assertOk()
        ->assertViewHas('stats')
        ->assertViewHas('stories');

    $stats = $resp->viewData('stats');
    expect($stats)->toHaveKeys(['alumni', 'donations', 'events', 'mentors']);
});

test('landing page stats are cached under landing_stats key', function () {
    Cache::forget('landing_stats');
    $this->get('/');

    expect(Cache::has('landing_stats'))->toBeTrue();
    $cached = Cache::get('landing_stats');
    expect($cached)->toHaveKey('alumni');
});

test('/ is named home and accessible', function () {
    expect(route('home'))->toContain('/');
    $this->get(route('home'))->assertOk();
});

test('OG and SEO meta tags are in the page', function () {
    $resp = $this->get('/');

    $resp->assertOk()
        ->assertSee('og:title', false)
        ->assertSee('og:description', false)
        ->assertSee('twitter:card', false)
        ->assertSee('description', false);
});

test('feedback route accessible from landing (no auth CSRF check)', function () {
    // Guest can POST feedback (no auth required, tested in FeedbackSurveysTest)
    // Here just verify the route name is embedded in the page or accessible
    $this->get('/')->assertSee('feedback', false);
});
