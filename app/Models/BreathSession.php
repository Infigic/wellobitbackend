<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreathSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_duration_seconds',
        'completed_at',
    ];
}
