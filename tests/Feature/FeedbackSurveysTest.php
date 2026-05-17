<?php

use App\Models\Feedback;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\User;

function verifiedUser(array $attrs = []): User
{
    return User::factory()->create(array_merge(['email_verified_at' => now()], $attrs));
}

// ── Feedback ──────────────────────────────────────────────────────────────────

test('authenticated user can submit feedback and it stores with their user_id', function () {
    $user = verifiedUser();

    $this->actingAs($user)->postJson(route('feedback.store'), [
        'category' => 'suggestion',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'message' => 'Here is my detailed suggestion.',
    ])->assertOk()->assertJson(['success' => true]);

    $fb = Feedback::first();
    expect($fb->user_id)->toBe($user->id);
    expect($fb->category)->toBe('suggestion');
});

test('guest can submit feedback without being logged in', function () {
    $this->postJson(route('feedback.store'), [
        'category' => 'general',
        'name' => 'Guest User',
        'email' => 'guest@example.com',
        'message' => 'Just some general thoughts here.',
    ])->assertOk();

    expect(Feedback::first()->user_id)->toBeNull();
});

test('feedback validates message length (min 10, max 5000)', function () {
    $this->postJson(route('feedback.store'), [
        'category' => 'bug',
        'name' => 'X',
        'email' => 'x@x.com',
        'message' => 'short',
    ])->assertStatus(422)->assertJsonValidationErrors('message');
});

test('feedback is rate limited at 10 per minute', function () {
    for ($i = 1; $i <= 10; $i++) {
        $this->postJson(route('feedback.store'), [
            'category' => 'general',
            'name' => 'U',
            'email' => 'u@u.com',
            'message' => 'This is a valid feedback message.',
        ])->assertOk();
    }
    $this->postJson(route('feedback.store'), [
        'category' => 'general', 'name' => 'U', 'email' => 'u@u.com',
        'message' => 'Over the limit.',
    ])->assertStatus(429);
});

// ── Surveys ────────────────────────────────────────────────────────────────────

test('surveys index shows audience-appropriate surveys', function () {
    $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
    Survey::factory()->create(['is_active' => true, 'target_audience' => 'alumni', 'created_by' => $admin->id]);
    Survey::factory()->create(['is_active' => true, 'target_audience' => 'all', 'created_by' => $admin->id]);

    $alumni = User::factory()->alumni()->create(['email_verified_at' => now()]);
    $student = User::factory()->student()->create(['email_verified_at' => now()]);

    $this->actingAs($alumni)->get(route('surveys.index'))
        ->assertInertia(fn ($p) => $p->has('surveys', 2));

    $this->actingAs($student)->get(route('surveys.index'))
        ->assertInertia(fn ($p) => $p->has('surveys', 1)
            ->where('surveys.0.completed', false));
});

test('user can respond to a survey and one-response is enforced', function () {
    $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
    $survey = Survey::factory()->create([
        'is_active' => true, 'target_audience' => 'all', 'created_by' => $admin->id,
    ]);
    $q1 = SurveyQuestion::factory()->create([
        'survey_id' => $survey->id, 'type' => 'text', 'order' => 0,
    ]);
    $q2 = SurveyQuestion::factory()->create([
        'survey_id' => $survey->id, 'type' => 'single_choice',
        'options' => ['A', 'B', 'C'], 'order' => 1,
    ]);

    $user = verifiedUser();

    $this->actingAs($user)->post(route('surveys.respond', $survey->id), [
        'answers' => [$q1->id => 'My text answer here.', $q2->id => 'B'],
    ])->assertRedirect(route('surveys.index'));

    expect(SurveyResponse::where('survey_id', $survey->id)->where('user_id', $user->id)->count())->toBe(2);

    // Re-submit → 409 or redirect with already-responded message
    $this->actingAs($user)->get(route('surveys.show', $survey->id))
        ->assertRedirect(route('surveys.index'));
});

test('multi_choice answers create one row per selection', function () {
    $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
    $survey = Survey::factory()->create(['is_active' => true, 'target_audience' => 'all', 'created_by' => $admin->id]);
    $q = SurveyQuestion::factory()->create([
        'survey_id' => $survey->id, 'type' => 'multi_choice',
        'options' => ['X', 'Y', 'Z'], 'order' => 0,
    ]);
    $user = verifiedUser();

    $this->actingAs($user)->post(route('surveys.respond', $survey->id), [
        'answers' => [$q->id => ['X', 'Z']],
    ])->assertRedirect();

    expect(SurveyResponse::where('question_id', $q->id)->count())->toBe(2);
});

// ── Admin surveys ─────────────────────────────────────────────────────────────

test('admin can create a survey with questions', function () {
    $admin = User::factory()->admin()->create(['email_verified_at' => now()]);

    $this->actingAs($admin)->post(route('admin.surveys.store'), [
        'title' => 'New Survey',
        'target_audience' => 'alumni',
        'is_active' => true,
        'questions' => [
            ['question' => 'How are you?', 'type' => 'text', 'options' => []],
            ['question' => 'Pick one', 'type' => 'single_choice', 'options' => ['Yes', 'No']],
        ],
    ])->assertRedirect(route('admin.surveys.index'));

    $survey = Survey::first();
    expect($survey->title)->toBe('New Survey');
    expect($survey->questions()->count())->toBe(2);
});

test('admin feedback index filters by category and resolved status', function () {
    Feedback::factory()->create(['category' => 'bug', 'is_resolved' => false]);
    Feedback::factory()->create(['category' => 'suggestion', 'is_resolved' => true]);

    $admin = User::factory()->admin()->create(['email_verified_at' => now()]);

    $this->actingAs($admin)->get(route('admin.feedback.index', ['category' => 'bug']))
        ->assertInertia(fn ($p) => $p->where('feedback.total', 1));

    $this->actingAs($admin)->get(route('admin.feedback.index', ['resolved' => 'resolved']))
        ->assertInertia(fn ($p) => $p->where('feedback.total', 1));
});

test('admin can toggle feedback resolved status', function () {
    $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
    $fb = Feedback::factory()->create(['is_resolved' => false]);

    $this->actingAs($admin)->post(route('admin.feedback.toggle', $fb->id))->assertRedirect();
    expect($fb->fresh()->is_resolved)->toBeTrue();

    $this->actingAs($admin)->post(route('admin.feedback.toggle', $fb->id))->assertRedirect();
    expect($fb->fresh()->is_resolved)->toBeFalse();
});

test('dashboard returns pendingSurveys for the authenticated user', function () {
    $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
    Survey::factory()->create(['is_active' => true, 'target_audience' => 'alumni', 'created_by' => $admin->id]);

    $alumni = User::factory()->alumni()->create(['email_verified_at' => now(), 'status' => 'approved']);

    $this->actingAs($alumni)->get(route('dashboard'))
        ->assertInertia(fn ($p) => $p->has('pendingSurveys', 1));
});
