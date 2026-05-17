<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SuccessStory extends Model
{
    /** @use HasFactory<\Database\Factories\SuccessStoryFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'headline',
        'slug',
        'body',
        'cover_image',
        'category',
        'status',
        'submitted_by',
        'reviewed_by',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (SuccessStory $story) {
            if (empty($story->slug)) {
                $base = Str::slug($story->headline);
                $slug = $base;
                while (SuccessStory::where('slug', $slug)->exists()) {
                    $slug = $base . '-' . Str::lower(Str::random(4));
                }
                $story->slug = $slug;
            }
        });
    }

    public function featuredUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
