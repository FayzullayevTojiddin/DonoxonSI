<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Data extends Model
{
    protected $fillable = ['name', 'key', 'value', 'status'];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function intents()
    {
        return self::where('status', true)
            ->get(['id', 'key']);
    }
}
