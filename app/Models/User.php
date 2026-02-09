<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_image',
        'role',
        'is_active',
        'otp',
        'otp_expires_at',
        'platform',
        'email_verified_at',
        'provider_id',
        'remember_token',
        'deleted_at',
        'updated_at',
        'created_at',
        'age',
        'gender',
        'activity_level',
        'reason',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'deleted_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    protected $appends = ['profile_image_url'];
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isCustomer()
    {
        return $this->role === 'customer';
    }
    public function getStatusClassNameAttribute()
    {
        return match ($this->is_active) {
            false => 'toggle-switch',
            true => 'toggle-switch-off',
        };
    }
    public function getProfileImageUrlAttribute()
    {
        return $this->profile_image
            ? asset('storage/' . $this->profile_image)
            : null;
    }

    public function tracking()
    {
        return $this->hasOne(UserTracking::class);
    }

    public function sessions()
    {
        return $this->hasMany(BreathSession::class, 'user_id');
    }

}
