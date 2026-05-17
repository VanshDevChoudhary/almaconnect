<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class Profile extends Model
{
    /** @use HasFactory<\Database\Factories\ProfileFactory> */
    use HasFactory, Searchable;

    protected $fillable = [
        'user_id',
        'slug',
        'batch',
        'branch',
        'roll_no',
        'current_company',
        'current_role',
        'industry',
        'city',
        'country',
        'bio',
        'skills',
        'linkedin_url',
        'website_url',
    ];

    protected $casts = [
        'skills' => 'array',
        'batch' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Profile $profile) {
            if (empty($profile->slug)) {
                $base = Str::slug(($profile->user?->name ?? 'member') . '-' . ($profile->batch ?? ''));
                $slug = $base;
                while (Profile::where('slug', $slug)->exists()) {
                    $slug = $base . '-' . Str::lower(Str::random(4));
                }
                $profile->slug = $slug;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function searchableAs(): string
    {
        return 'profiles_index';
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $this->loadMissing('user');

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->user?->name,
            'slug' => $this->slug,
            'batch' => $this->batch,
            'branch' => $this->branch,
            'industry' => $this->industry,
            'city' => $this->city,
            'country' => $this->country,
            'current_company' => $this->current_company,
            'current_role' => $this->current_role,
            'bio' => $this->bio,
            'skills' => $this->skills ?? [],
            'avatar' => $this->user?->avatar,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->user && $this->user->status === 'approved';
    }
}

