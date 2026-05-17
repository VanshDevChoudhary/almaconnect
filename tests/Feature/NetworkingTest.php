<?php

use App\Models\Comment;
use App\Models\Group;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function approvedUser(array $attrs = []): User
{
    return User::factory()->alumni()->create(array_merge(['email_verified_at' => now()], $attrs));
}

function makeGroup(?User $creator = null, array $attrs = []): Group
{
    $creator ??= approvedUser();

    return Group::factory()->create(array_merge([
        'created_by' => $creator->id,
        'is_public' => true,
        'type' => 'interest',
    ], $attrs));
}

function joinGroup(Group $group, User $user, string $role = 'member'): void
{
    $group->members()->attach($user->id, ['role' => $role, 'joined_at' => now()]);
}

test('groups index lists public groups and filters by type', function () {
    $viewer = approvedUser();
    makeGroup(null, ['name' => 'Alpha Regional', 'type' => 'regional']);
    makeGroup(null, ['name' => 'Beta Interest', 'type' => 'interest']);

    $this->actingAs($viewer)->get(route('groups.index'))
        ->assertInertia(fn ($p) => $p->component('Groups/Index')->has('groups', 2));

    $this->actingAs($viewer)->get(route('groups.index', ['type' => 'regional']))
        ->assertInertia(fn ($p) => $p->has('groups', 1)
            ->where('groups.0.name', 'Alpha Regional'));

    $this->actingAs($viewer)->get(route('groups.index', ['q' => 'Beta']))
        ->assertInertia(fn ($p) => $p->has('groups', 1)
            ->where('groups.0.name', 'Beta Interest'));
});

test('join and leave a group updates membership', function () {
    $user = approvedUser();
    $group = makeGroup();

    $this->actingAs($user)->post(route('groups.join', $group->slug))->assertRedirect();
    expect($user->isMemberOf($group->id))->toBeTrue();

    $this->actingAs($user)->post(route('groups.leave', $group->slug))->assertRedirect();
    expect($user->fresh()->isMemberOf($group->id))->toBeFalse();
});

test('the group creator cannot leave their own group', function () {
    $creator = approvedUser();
    $group = makeGroup($creator);
    joinGroup($group, $creator);

    $this->actingAs($creator)->post(route('groups.leave', $group->slug));

    expect($creator->isMemberOf($group->id))->toBeTrue();
});

test('a member can post and the body is sanitized', function () {
    $user = approvedUser();
    $group = makeGroup();
    joinGroup($group, $user);

    $this->actingAs($user)->post(route('posts.store', $group->slug), [
        'body' => '<script>alert("xss")</script>**hello** world',
    ])->assertRedirect();

    $post = Post::first();
    expect($post)->not->toBeNull();
    expect($post->body)->not->toContain('<script');
    expect($post->body)->toContain('hello');
});

test('a non-member cannot post in a group', function () {
    $user = approvedUser();
    $group = makeGroup();

    $this->actingAs($user)
        ->post(route('posts.store', $group->slug), ['body' => 'hi'])
        ->assertForbidden();

    expect(Post::count())->toBe(0);
});

test('posting with an image stores a resized jpeg', function () {
    Storage::fake('public');
    $user = approvedUser();
    $group = makeGroup();
    joinGroup($group, $user);

    $this->actingAs($user)->post(route('posts.store', $group->slug), [
        'body' => 'with image',
        'image' => UploadedFile::fake()->image('photo.png', 2000, 1500),
    ])->assertRedirect();

    $post = Post::first();
    expect($post->image)->toStartWith('posts/');
    Storage::disk('public')->assertExists($post->image);
});

test('members can comment and non-members cannot', function () {
    $member = approvedUser();
    $stranger = approvedUser();
    $group = makeGroup();
    joinGroup($group, $member);
    $post = Post::factory()->create(['group_id' => $group->id, 'user_id' => $member->id]);

    $this->actingAs($member)
        ->post(route('comments.store', $post->id), ['body' => 'nice <b>post</b>'])
        ->assertRedirect();
    expect(Comment::first()->body)->toBe('nice post'); // tags stripped

    $this->actingAs($stranger)
        ->post(route('comments.store', $post->id), ['body' => 'sneaky'])
        ->assertForbidden();
});

test('like toggle returns json and flips state', function () {
    $user = approvedUser();
    $group = makeGroup();
    joinGroup($group, $user);
    $post = Post::factory()->create(['group_id' => $group->id, 'user_id' => $user->id]);

    $r1 = $this->actingAs($user)->postJson(route('posts.like', $post->id));
    $r1->assertOk()->assertJson(['liked' => true, 'likes_count' => 1]);

    $r2 = $this->actingAs($user)->postJson(route('posts.like', $post->id));
    $r2->assertOk()->assertJson(['liked' => false, 'likes_count' => 0]);

    expect(Like::count())->toBe(0);
});

test('post deletion is authorised: owner, moderator and admin only', function () {
    $owner = approvedUser();
    $stranger = approvedUser();
    $moderator = approvedUser();
    $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
    $group = makeGroup();
    joinGroup($group, $owner);
    joinGroup($group, $moderator, 'moderator');

    $post = Post::factory()->create(['group_id' => $group->id, 'user_id' => $owner->id]);

    // Stranger forbidden
    $this->actingAs($stranger)->delete(route('posts.destroy', $post->id))->assertForbidden();
    expect(Post::find($post->id))->not->toBeNull();

    // Moderator allowed
    $this->actingAs($moderator)->delete(route('posts.destroy', $post->id))->assertRedirect();
    expect(Post::find($post->id))->toBeNull();

    // Admin allowed on another post
    $post2 = Post::factory()->create(['group_id' => $group->id, 'user_id' => $owner->id]);
    $this->actingAs($admin)->delete(route('posts.destroy', $post2->id))->assertRedirect();
    expect(Post::find($post2->id))->toBeNull();
});

test('only moderators or admins can pin a post', function () {
    $owner = approvedUser();
    $moderator = approvedUser();
    $group = makeGroup();
    joinGroup($group, $owner);
    joinGroup($group, $moderator, 'moderator');
    $post = Post::factory()->create(['group_id' => $group->id, 'user_id' => $owner->id, 'is_pinned' => false]);

    $this->actingAs($owner)->post(route('posts.pin', $post->id))->assertForbidden();

    $this->actingAs($moderator)->post(route('posts.pin', $post->id))->assertRedirect();
    expect($post->fresh()->is_pinned)->toBeTrue();

    $this->actingAs($moderator)->post(route('posts.unpin', $post->id))->assertRedirect();
    expect($post->fresh()->is_pinned)->toBeFalse();
});

test('feed sorts pinned posts first then newest', function () {
    $user = approvedUser();
    $group = makeGroup();
    joinGroup($group, $user);

    $old = Post::factory()->create(['group_id' => $group->id, 'user_id' => $user->id, 'created_at' => now()->subDays(3)]);
    $new = Post::factory()->create(['group_id' => $group->id, 'user_id' => $user->id, 'created_at' => now()->subDay()]);
    $pinned = Post::factory()->create(['group_id' => $group->id, 'user_id' => $user->id, 'is_pinned' => true, 'created_at' => now()->subDays(5)]);

    $this->actingAs($user)->get(route('groups.show', $group->slug))
        ->assertInertia(fn ($p) => $p
            ->where('posts.data.0.id', $pinned->id)
            ->where('posts.data.1.id', $new->id)
            ->where('posts.data.2.id', $old->id)
        );
});

test('post creation is rate limited at 30 per hour', function () {
    $user = approvedUser();
    $group = makeGroup();
    joinGroup($group, $user);

    for ($i = 1; $i <= 30; $i++) {
        $this->actingAs($user)
            ->post(route('posts.store', $group->slug), ['body' => "post {$i}"])
            ->assertRedirect();
    }

    $this->actingAs($user)
        ->post(route('posts.store', $group->slug), ['body' => 'over the limit'])
        ->assertStatus(429);
});
