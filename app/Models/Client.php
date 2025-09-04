<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'street',
        'city',
        'state',
        'postal_code',
        'country',
        'user_id',
    ];
}
