<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NumberSequence extends Model
{
    protected $fillable = ['sequence_key', 'prefix', 'year', 'next_number'];

    protected $casts = [
        'year' => 'integer',
        'next_number' => 'integer',
    ];
}
