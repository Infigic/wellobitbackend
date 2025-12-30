<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyQuote extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote',
        'type',
        'status',
        'state',  // NEW
    ];

    // Scope: Get quotes for specific state
    public function scopeForState($query, string $state)
    {
        return $query->where('state', $state);
    }

    // Scope: Get active quotes
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }
}
