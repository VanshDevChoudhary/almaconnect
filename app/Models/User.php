<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'google_id',
        'avatar',
        'email_verified_at',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        // Keep the search index in sync when approval status changes:
        // approving makes a profile searchable, banning/rejecting removes it.
        static::updated(function (User $user) {
            if ($user->wasChanged('status')) {
                $user->profile?->searchable();
            }
        });
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_members')
            ->withPivot('role', 'joined_at');
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_rsvps')
            ->withPivot('status')
            ->withTimestamps();
    }

    public function jobsPosted(): HasMany
    {
        return $this->hasMany(Job::class, 'posted_by');
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    public function submittedStories(): HasMany
    {
        return $this->hasMany(SuccessStory::class, 'submitted_by');
    }

    public function featuredStories(): HasMany
    {
        return $this->hasMany(SuccessStory::class, 'user_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function isMemberOf(int $groupId): bool
    {
        return $this->groups()->whereKey($groupId)->exists();
    }

    public function isModeratorOf(int $groupId): bool
    {
        return $this->groups()
            ->whereKey($groupId)
            ->wherePivot('role', 'moderator')
            ->exists();
    }
}
