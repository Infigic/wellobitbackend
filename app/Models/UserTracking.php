<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'user_id',
        'device_id',
        'first_name',
        'signup_method',
        'signup_source',
        'installed_at',
        'registered_at',
        'consent_email',
        'primary_reason_to_use',
        'first_breath_session_at',
        'last_active_at',
        'trial_started_at',
        'trial_ends_at',
        'paid_started_at',
        'has_apple_watch',
        'apple_health_connected',
        'current_plan',
        'is_paid',
    ];

    protected $casts = [
        'installed_at'  => 'datetime',
        'registered_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(UserDevice::class, 'device_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
