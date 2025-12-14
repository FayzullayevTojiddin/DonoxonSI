<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotFoundData extends Model
{
    protected $fillable = [
        'intent',
        'details_from',
        'status',
    ];

    protected $casts = [
        'details_from' => 'array',
        'status' => 'boolean',
    ];
}
