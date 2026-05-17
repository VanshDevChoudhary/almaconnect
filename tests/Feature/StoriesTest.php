<?php

use App\Models\SuccessStory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function storyAlumni(): User
{
    return User::factory()->alumni()->create(['email_verified_at' => now()]);
}

function storyAdmin(): User
{
    return User::factory()->admin()->create(['email_verified_at' => now()]);
}

function longBody(): string
{
    return str_repeat('This is a meaningful sentence about an inspiring journey. ', 20);
}

test('stories index shows published only and is reachable by pending users', function () {
    $author = storyAlumni();
    SuccessStory::factory(3)->published()->create(['user_id' => $author->id]);
    SuccessStory::factory(2)->pending()->create(['user_id' => $author->id]);

    $this->get(route('stories.index'))->assertRedirect(route('login'));

    $pending = User::factory()->create(['status' => 'pending', 'email_verified_at' => now()]);
    $this->actingAs($pending)->get(route('stories.index'))
        ->assertInertia(fn ($p) => $p->component('Stories/Index'));

    // 3 published total → 1 featured + 2 in grid
    $this->actingAs(storyAlumni())->get(route('stories.index'))
        ->assertInertia(fn ($p) => $p->has('stories', 2)->has('featured'));
});

test('category filter narrows the index', function () {
    $a = storyAlumni();
    SuccessStory::factory(2)->published()->create(['user_id' => $a->id, 'category' => 'research']);
    SuccessStory::factory(1)->published()->create(['user_id' => $a->id, 'category' => 'career']);

    $this->actingAs($a)->get(route('stories.index', ['category' => 'career']))
        ->assertInertia(fn ($p) => $p->where('featured.category', 'career')->has('stories', 0));
});

test('story detail loads a published story and 404s an unpublished one', function () {
    $a = storyAlumni();
    $pub = SuccessStory::factory()->published()->create(['user_id' => $a->id]);
    $pending = SuccessStory::factory()->pending()->create(['user_id' => $a->id]);

    $this->actingAs($a)->get(route('stories.show', $pub->slug))
        ->assertInertia(fn ($p) => $p->component('Stories/Show')->where('story.slug', $pub->slug));

    $this->actingAs($a)->get(route('stories.show', $pending->slug))->assertNotFound();
});

test('students cannot submit stories, alumni can', function () {
    $student = User::factory()->student()->create(['email_verified_at' => now()]);
    $this->actingAs($student)->get(route('stories.submit'))->assertForbidden();
    $this->actingAs($student)->post(route('stories.store'), [])->assertForbidden();

    $this->actingAs(storyAlumni())->get(route('stories.submit'))->assertOk();
});

test('alumni submission creates a pending story owned by the submitter', function () {
    Storage::fake('public');
    $a = storyAlumni();

    $this->actingAs($a)->post(route('stories.store'), [
        'headline' => 'My Founder Journey',
        'category' => 'entrepreneurship',
        'cover_image' => UploadedFile::fake()->image('cover.jpg', 1600, 900),
        'body' => longBody().'<script>alert(1)</script>',
    ])->assertRedirect();

    $story = SuccessStory::first();
    expect($story->status)->toBe('pending');
    expect($story->submitted_by)->toBe($a->id);
    expect($story->user_id)->toBe($a->id);
    expect($story->slug)->toBe('my-founder-journey');
    expect($story->body)->not->toContain('<script');
    Storage::disk('public')->assertExists($story->cover_image);
});

test('submission validation enforces cover image and minimum body length', function () {
    $a = storyAlumni();

    $this->actingAs($a)->post(route('stories.store'), [
        'headline' => 'Too short',
        'category' => 'career',
        'body' => 'short',
    ])->assertSessionHasErrors(['cover_image', 'body']);
});

test('mine shows the submitter own stories across statuses', function () {
    $me = storyAlumni();
    $other = storyAlumni();
    SuccessStory::factory()->pending()->create(['user_id' => $me->id, 'submitted_by' => $me->id]);
    SuccessStory::factory()->published()->create(['user_id' => $me->id, 'submitted_by' => $me->id]);
    SuccessStory::factory()->published()->create(['user_id' => $other->id, 'submitted_by' => $other->id]);

    $this->actingAs($me)->get(route('stories.mine'))
        ->assertInertia(fn ($p) => $p->component('Stories/Mine')->has('stories', 2));
});

test('admin approve publishes a pending story; reject hides it', function () {
    $admin = storyAdmin();
    $a = storyAlumni();
    $story = SuccessStory::factory()->pending()->create(['user_id' => $a->id, 'submitted_by' => $a->id]);

    $this->actingAs($admin)->post(route('admin.stories.approve', $story->id))->assertRedirect();
    $story->refresh();
    expect($story->status)->toBe('published');
    expect($story->published_at)->not->toBeNull();
    expect($story->reviewed_by)->toBe($admin->id);

    $this->actingAs($a)->get(route('stories.index'))
        ->assertInertia(fn ($p) => $p->where('featured.headline', $story->headline));

    // Reject another
    $story2 = SuccessStory::factory()->pending()->create(['user_id' => $a->id]);
    $this->actingAs($admin)->post(route('admin.stories.reject', $story2->id))->assertRedirect();
    expect($story2->fresh()->status)->toBe('rejected');
});

test('admin direct create publishes immediately', function () {
    Storage::fake('public');
    $admin = storyAdmin();
    $featured = storyAlumni();

    $this->actingAs($admin)->post(route('admin.stories.store'), [
        'headline' => 'Featured Spotlight',
        'category' => 'research',
        'cover_image' => UploadedFile::fake()->image('c.jpg', 1600, 900),
        'body' => longBody(),
        'user_id' => $featured->id,
    ])->assertRedirect(route('admin.stories.index'));

    $story = SuccessStory::first();
    expect($story->status)->toBe('published');
    expect($story->published_at)->not->toBeNull();
    expect($story->user_id)->toBe($featured->id);
});

test('non-admins cannot reach admin story routes', function () {
    $a = storyAlumni();
    $this->actingAs($a)->get(route('admin.stories.index'))->assertForbidden();
    $story = SuccessStory::factory()->pending()->create(['user_id' => $a->id]);
    $this->actingAs($a)->post(route('admin.stories.approve', $story->id))->assertForbidden();
});

test('slug stays stable when an admin edits the headline', function () {
    Storage::fake('public');
    $admin = storyAdmin();
    $a = storyAlumni();
    $story = SuccessStory::factory()->published()->create(['user_id' => $a->id, 'headline' => 'Original']);
    $original = $story->slug;

    $this->actingAs($admin)->patch(route('admin.stories.update', $story->id), [
        'headline' => 'A Totally New Headline',
        'category' => $story->category,
        'body' => longBody(),
        'user_id' => $a->id,
        'status' => 'published',
    ])->assertRedirect();

    $story->refresh();
    expect($story->headline)->toBe('A Totally New Headline');
    expect($story->slug)->toBe($original);
});
