<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Job extends Model
{
    /** @use HasFactory<\Database\Factories\JobFactory> */
    use HasFactory;

    protected $table = 'jobs_listings';

    protected $fillable = [
        'posted_by',
        'title',
        'company',
        'location',
        'type',
        'description',
        'skills',
        'salary_min',
        'salary_max',
        'salary_currency',
        'apply_url',
        'apply_email',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'skills' => 'array',
        'expires_at' => 'datetime',
    ];

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')->where('expires_at', '>', now());
    }
}
