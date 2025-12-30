<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    /* protected $fillable = [
        'quote',
        'type',  // Changed from quote_person
        'is_active',  // Changed from status
    ]; */

    protected $casts = [
        'is_active' => 'boolean',  // Cast the boolean
    ];

    const USER_MOOD_TYPES = [
        1 => 'LOW',
        2 => 'MODERATE',
        3 => 'BALANCED',
        4 => 'GOOD',
    ];

    public function getTypeNameAttribute()
    {
        return match ($this->type) {
            1 => ucfirst('Low'),
            2 => ucfirst('Moderate'),
            3 => ucfirst('Balanced'),
            4 => ucfirst('Good'),
            default => 'Unknown',
        };
    }

    public function getTypeClassNameAttribute()
    {
        return match ($this->type) {
            1 => 'success',
            2 => 'warning',
            3 => 'danger',
            default => 'info',
        };
    }

    public function getStatusClassNameAttribute()
    {
        return match ($this->is_active) {
            false => 'toggle-switch',
            true => 'toggle-switch-off',
        };
    }
}
