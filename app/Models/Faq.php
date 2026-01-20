<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'image_url',
        'category_id',
    ];

    // Each FAQ belongs to one category
    public function category()
    {
        return $this->belongsTo(FaqCategory::class, 'category_id');
    }
}
