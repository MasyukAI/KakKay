<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\Activitylog\Models\Activity as BaseActivity;

final class Activity extends BaseActivity
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';
}
