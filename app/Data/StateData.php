<?php

namespace App\Data;

class StateData
{
    /**
     * Malaysian states.
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
     * Get all states as key-value pairs (name => name).
     */
    public static function getStatesOptions(): array
    {
        return array_combine(self::$states, self::$states);
    }

    /**
     * Get all states.
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
