<?php

declare(strict_types=1);

namespace App\Data;

final class StateData
{
    /**
     * Malaysian states.
     *
     * @var array<string>
     */
    public static array $states = [
        'Johor',
        'Kedah',
        'Kelantan',
        'Kuala Lumpur',
        'Labuan',
        'Melaka',
        'Negeri Sembilan',
        'Pahang',
        'Perak',
        'Perlis',
        'Pulau Pinang',
        'Putrajaya',
        'Sabah',
        'Sarawak',
        'Selangor',
        'Terengganu',
    ];

    /**
     * Get state options for forms.
     *
     * @return array<string, string>
     */
    public static function getStatesOptions(): array
    {
        return array_combine(self::$states, self::$states);
    }

    /**
     * Get all states.
     *
     * @return array<string, string>
     */
    public static function getStates(): array
    {
        return self::$states;
    }

    /**
     * Check if a state exists.
     */
    public static function exists(string $state): bool
    {
        return in_array($state, self::$states);
    }
}
