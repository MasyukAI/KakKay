<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carting extends Model
{
    protected $casts = [
        'items' => 'array',
        'conditions' => 'array',
    ];
}
