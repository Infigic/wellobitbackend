<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hrv extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sample_id',
        'datetime',
        'device_timestamp',
        'hrv',
        'sdnn',
        'status',
        'baseline_value',
    ];

    protected $casts = [
        'datetime' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusNameAttribute()
    {
        return match ($this->status) {
            1 => ucfirst('Low'),
            2 => ucfirst('Moderate'),
            3 => ucfirst('Balanced'),
            4 => ucfirst('Good'),
            default => 'Unknown',
        };
    }

    public function getStatusClassNameAttribute()
    {
        return match ($this->status) {
            1 => 'success',
            2 => 'warning',
            3 => 'danger',
            default => 'info',
        };
    }
}
