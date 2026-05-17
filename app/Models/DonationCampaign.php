<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class DonationCampaign extends Model
{
    /** @use HasFactory<\Database\Factories\DonationCampaignFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'cover_image',
        'target_amount',
        'raised_amount',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'target_amount' => 'decimal:2',
        'raised_amount' => 'decimal:2',
        'ends_at' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (DonationCampaign $campaign) {
            if (empty($campaign->slug)) {
                $base = Str::slug($campaign->title);
                $slug = $base;
                while (DonationCampaign::where('slug', $slug)->exists()) {
                    $slug = $base . '-' . Str::lower(Str::random(4));
                }
                $campaign->slug = $slug;
            }
        });
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class, 'campaign_id');
    }

    public function successfulDonations(): HasMany
    {
        return $this->hasMany(Donation::class, 'campaign_id')->where('status', 'success');
    }
}
