<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyResponse extends Model
{
    /** @use HasFactory<\Database\Factories\SurveyResponseFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'survey_id',
        'question_id',
        'user_id',
        'answer',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(SurveyQuestion::class, 'question_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
