<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class Carting extends Model
{
    protected $casts = [
        'items' => 'array',
        'conditions' => 'array',
    ];
}
