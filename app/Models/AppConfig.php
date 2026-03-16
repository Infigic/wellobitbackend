<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'config_key',
        'config_value',
        'value_type',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getParsedValueAttribute()
    {
        if ($this->config_value === null) {
            return null;
        }

        return match ($this->value_type) {
            'boolean' => filter_var($this->config_value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $this->config_value,
            'json'    => json_decode($this->config_value, true),
            default   => $this->config_value,
        };
    }
}