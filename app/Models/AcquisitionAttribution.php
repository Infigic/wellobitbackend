<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcquisitionAttribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'user_tracking_id',
        'acquisition_channel',
        'acquisition_source',
        'campaign_name',
    ];
}
