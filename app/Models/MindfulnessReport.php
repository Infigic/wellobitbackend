<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MindfulnessReport extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'timestamp' => 'datetime',
    ];
}
