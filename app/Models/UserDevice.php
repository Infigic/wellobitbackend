<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'anonymous_id',
        'timezone',
        'locale',
        'app_version',
        'apple_watch_os_version',
        'apple_watch_model'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trackings()
    {
        return $this->hasMany(UserTracking::class, 'device_id');
    }
}
