<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    protected $fillable = [
        'readed',
        'full_name',
        'request',
        'details_from',
        'where'
    ];

    protected $casts = [
        'readed' => 'boolean',
        'details_from' => 'array',
    ];
}