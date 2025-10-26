<?php

declare(strict_types=1);

namespace App\Models;

use App\Data\DistrictData;
use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

final class District extends Model
{
    use Sushi;

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'state',
        'name',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'state' => 'string',
        'id' => 'string',
    ];

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Get districts by state name.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, District>
     */
    public static function getByState(string $state): \Illuminate\Database\Eloquent\Collection
    {
        return self::query()->forState($state)->orderBy('name')->get();
    }

    /**
     * Get districts options for a specific state (for select dropdowns).
     *
     * @return array<string, string>
     */
    public static function getByStateOptions(string $state): array
    {
        return self::query()->forState($state)
            ->orderBy('name')
            ->pluck('name', 'name')
            ->toArray();
    }

    /**
     * Get the district data for Sushi.
     *
     * @return array<array<string, mixed>>
     */
    public function getRows(): array
    {
        $rows = [];
        $id = 1;

        foreach (DistrictData::$districts as $state => $districts) {
            foreach ($districts as $district) {
                $rows[] = [
                    'id' => (string) $id++,
                    'state' => $state,
                    'name' => $district,
                ];
            }
        }

        return $rows;
    }

    /**
     * Scope a query to only include districts for a specific state name.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<District>  $query
     * @return \Illuminate\Database\Eloquent\Builder<District>
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function forState($query, string $state): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('state', $state);
    }
}
