<?php

declare(strict_types=1);

namespace MasyukAI\Stock\Tests\Support;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use MasyukAI\Stock\Traits\HasStock;

class TestProduct extends Model
{
    use HasStock, HasUuids;

    protected $table = 'test_products';

    protected $fillable = ['name'];
}
