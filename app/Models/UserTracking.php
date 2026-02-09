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
        'first_breath_session_at' => 'datetime',
        'last_active_at' => 'datetime',
        'trial_started_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'paid_started_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(UserDevice::class, 'device_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function acquisition()
    {
        return $this->hasOne(AcquisitionAttribution::class, 'user_tracking_id');
    }
    

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('installed_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeActivateToday($query)
    {
        return $query->whereDate('installed_at', today());
    }

    public function scopeActivated($query)
    {
        return $query->whereNotNull('first_breath_session_at');
    }

    public function scopeTrial($query)
    {
        return $query->where('trial_ends_at', '>', now())
            ->whereNotNull('trial_started_at')
            ->where('is_paid', false);
    }

    public function scopePaid($query)
    {
        return $query->where('is_paid', true)
            ->whereNotNull('paid_started_at');
    }

    public function scopeExpiredTrial($query)
    {
        return $query->where('trial_ends_at', '<', now())
            ->whereNotNull('trial_started_at')
            ->where('is_paid', false);
    }

    public function scopeTrialExpiredButActive($query)
    {
        return $query
            ->where('is_paid', false)
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', now());
    }

    public function scopeConvertedToPaid($query)
    {
        return $query->where('is_paid', true)
            ->whereNotNull('paid_started_at')
            ->whereNotNull('trial_started_at');
    }

    public function scopeExpiredTriallsSoon($query, $days)
    {
        return $query
            ->where('is_paid', false)
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>=', now())
            ->where('trial_ends_at', '<=', now()->addDays($days));
    }

    public function scopeInActiveForDays($query, $days, $maxDays = null)
    {
        $query->where('last_active_at', '<=', now()->subDays($days));

        if ($maxDays) {
            $query->where('last_active_at', '>', now()->subDays($maxDays));
        }

        return $query;
    }

    public function scopeIncompletedSetup($query)
    {
        return $query
            ->where('has_apple_watch', false);
    }
}
