<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory;

    protected $table = 'subscriptions';

    protected $fillable = [
        'user_id',
        'plan_name',
        'trial_started_at',
        'trial_ends_at',
        'paid_started_at',
    ];

    protected $casts = [
        'trial_started_at' => 'datetime',
        'trial_ends_at'    => 'datetime',
        'paid_started_at'  => 'datetime',
    ];

    /**
     * Relationship: subscription belongs to a user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
