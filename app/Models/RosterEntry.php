<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RosterEntry extends Model
{
    /** @use HasFactory<\Database\Factories\RosterEntryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'batch',
        'branch',
        'roll_no',
    ];

    protected $casts = [
        'batch' => 'integer',
    ];
}
