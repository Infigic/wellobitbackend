<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEarnedBadge extends Model
{
    use HasFactory;

    protected $table = 'user_earned_badges';

    protected $fillable = [
        'user_id',
        'badge_id',
        'badge_title',
        'badge_subtitle',
        'earned_at',
    ];

    protected $casts = [
        'earned_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
