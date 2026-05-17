<?php

use App\Models\Job;
use App\Models\User;
use Illuminate\Support\Carbon;

function alumni(array $attrs = []): User
{
    return User::factory()->alumni()->create(array_merge(['email_verified_at' => now()], $attrs));
}

function student(): User
{
    return User::factory()->student()->create(['email_verified_at' => now()]);
}

function admin(): User
{
    return User::factory()->admin()->create(['email_verified_at' => now()]);
}

function jobPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'DevOps Engineer',
        'company' => 'Acme',
        'location' => 'Remote',
        'type' => 'full_time',
        'description' => 'Build **CI/CD** pipelines.',
        'skills' => ['Docker', 'AWS'],
        'salary_min' => 2000000,
        'salary_max' => 3500000,
        'salary_currency' => 'INR',
        'apply_email' => 'careers@acme.com',
        'expires_at' => now()->addDays(30)->toDateString(),
    ], $overrides);
}

test('jobs index shows only active, non-expired listings', function () {
    $u = alumni();
    Job::factory(3)->active()->create(['posted_by' => $u->id]);
    Job::factory(2)->filled()->create(['posted_by' => $u->id]);
    Job::factory(2)->expired()->create(['posted_by' => $u->id]);

    $this->get(route('jobs.index'))->assertRedirect(route('login'));

    $pending = User::factory()->create(['status' => 'pending', 'email_verified_at' => now()]);
    $this->actingAs($pending)->get(route('jobs.index'))->assertRedirect(route('pending-review'));

    $this->actingAs($u)->get(route('jobs.index'))
        ->assertInertia(fn ($p) => $p->component('Jobs/Index')->where('jobs.total', 3));
});

test('filter by type and search by company narrow results', function () {
    $u = alumni();
    Job::factory()->active()->create(['posted_by' => $u->id, 'type' => 'internship', 'company' => 'Zeta']);
    Job::factory()->active()->create(['posted_by' => $u->id, 'type' => 'full_time', 'company' => 'Google']);

    $this->actingAs($u)->get(route('jobs.index', ['type' => ['internship']]))
        ->assertInertia(fn ($p) => $p->where('jobs.total', 1));

    $this->actingAs($u)->get(route('jobs.index', ['q' => 'Google']))
        ->assertInertia(fn ($p) => $p->where('jobs.total', 1)
            ->where('jobs.data.0.company', 'Google'));
});

test('students cannot post jobs but alumni can', function () {
    $this->actingAs(student())->get(route('jobs.create'))->assertForbidden();
    $this->actingAs(student())->post(route('jobs.store'), jobPayload())->assertForbidden();

    $this->actingAs(student())->get(route('jobs.index'))
        ->assertInertia(fn ($p) => $p->where('canPost', false));

    $this->actingAs(alumni())->get(route('jobs.create'))->assertOk();
});

test('alumni can post a job and posted_by is set server-side', function () {
    $u = alumni();

    $this->actingAs($u)
        ->post(route('jobs.store'), jobPayload(['posted_by' => 99999]))
        ->assertRedirect();

    $job = Job::first();
    expect($job->posted_by)->toBe($u->id);
    expect($job->status)->toBe('active');
    expect($job->description)->not->toContain('<script');
});

test('server requires an apply url or email', function () {
    $u = alumni();

    $this->actingAs($u)
        ->post(route('jobs.store'), jobPayload(['apply_email' => null, 'apply_url' => null]))
        ->assertSessionHasErrors(['apply_url', 'apply_email']);
});

test('server rejects salary_max below salary_min and bad expiry', function () {
    $u = alumni();

    $this->actingAs($u)
        ->post(route('jobs.store'), jobPayload(['salary_min' => 5000000, 'salary_max' => 1000000]))
        ->assertSessionHasErrors('salary_max');

    $this->actingAs($u)
        ->post(route('jobs.store'), jobPayload(['expires_at' => now()->toDateString()]))
        ->assertSessionHasErrors('expires_at');

    $this->actingAs($u)
        ->post(route('jobs.store'), jobPayload(['expires_at' => now()->addDays(120)->toDateString()]))
        ->assertSessionHasErrors('expires_at');
});

test('job edit is owner or admin only', function () {
    $owner = alumni();
    $other = alumni();
    $admin = admin();
    $job = Job::factory()->active()->create(['posted_by' => $owner->id]);

    $this->actingAs($other)->get(route('jobs.edit', $job->id))->assertForbidden();
    $this->actingAs($owner)->get(route('jobs.edit', $job->id))->assertOk();

    $this->actingAs($admin)
        ->patch(route('jobs.update', $job->id), jobPayload(['title' => 'Updated by admin']))
        ->assertRedirect();
    expect($job->fresh()->title)->toBe('Updated by admin');
});

test('mark filled removes the job from the default browse view', function () {
    $owner = alumni();
    $job = Job::factory()->active()->create(['posted_by' => $owner->id]);

    $this->actingAs($owner)->post(route('jobs.mark-filled', $job->id))->assertRedirect();
    expect($job->fresh()->status)->toBe('filled');

    $this->actingAs($owner)->get(route('jobs.index'))
        ->assertInertia(fn ($p) => $p->where('jobs.total', 0));
});

test('mine returns only the current user posts across statuses', function () {
    $me = alumni();
    $other = alumni();
    Job::factory(2)->active()->create(['posted_by' => $me->id]);
    Job::factory()->filled()->create(['posted_by' => $me->id]);
    Job::factory(3)->active()->create(['posted_by' => $other->id]);

    $this->actingAs($me)->get(route('jobs.mine'))
        ->assertInertia(fn ($p) => $p->component('Jobs/Mine')->has('jobs', 3));
});

test('jobs:expire command expires past-due active jobs and is idempotent', function () {
    $u = alumni();
    $job = Job::factory()->active()->create(['posted_by' => $u->id]);
    $job->forceFill(['expires_at' => now()->subDay()])->save();
    Job::factory()->active()->create(['posted_by' => $u->id]); // still valid

    $this->artisan('jobs:expire')->expectsOutputToContain('Expired 1 jobs.')->assertExitCode(0);
    expect($job->fresh()->status)->toBe('expired');

    $this->artisan('jobs:expire')->expectsOutputToContain('Expired 0 jobs.');
});

test('job posting is rate limited at 5 per hour', function () {
    $u = alumni();

    for ($i = 1; $i <= 5; $i++) {
        $this->actingAs($u)
            ->post(route('jobs.store'), jobPayload(['title' => "Role {$i}"]))
            ->assertRedirect();
    }

    $this->actingAs($u)
        ->post(route('jobs.store'), jobPayload(['title' => 'Over limit']))
        ->assertStatus(429);
});
