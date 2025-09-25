<?php

namespace App\Data;

class DistrictData
{
    /**
     * Districts organized by state.
     */
    public static array $districts = [
        'Johor' => [
            'Johor Bahru',
            'Batu Pahat',
            'Kluang',
            'Kulai',
            'Muar',
            'Kota Tinggi',
            'Segamat',
            'Pontian',
            'Tangkak',
            'Mersing',
        ],
        'Kedah' => [
            'Kuala Muda',
            'Kota Setar',
            'Kulim',
            'Kubang Pasu',
            'Baling',
            'Pendang',
            'Langkawi',
            'Yan',
            'Sik',
            'Padang Terap',
            'Pokok Sena',
            'Bandar Baharu',
        ],
        'Kelantan' => [
            'Kota Bharu',
            'Pasir Mas',
            'Tumpat',
            'Bachok',
            'Tanah Merah',
            'Pasir Puteh',
            'Kuala Krai',
            'Machang',
            'Gua Musang',
            'Jeli',
            'Lojing',
        ],
        'Melaka' => [
            'Melaka Tengah',
            'Alor Gajah',
            'Jasin',
        ],
        'Negeri Sembilan' => [
            'Seremban',
            'Jempol',
            'Port Dickson',
            'Tampin',
            'Kuala Pilah',
            'Rembau',
            'Jelebu',
        ],
        'Pahang' => [
            'Kuantan',
            'Temerloh',
            'Bentong',
            'Maran',
            'Rompin',
            'Pekan',
            'Bera',
            'Raub',
            'Jerantut',
            'Lipis',
            'Cameron Highlands',
        ],
        'Perak' => [
            'Kinta',
            'Larut, Matang dan Selama',
            'Manjung',
            'Hilir Perak',
            'Kerian',
            'Batang Padang',
            'Kuala Kangsar',
            'Perak Tengah',
            'Hulu Perak',
            'Kampar',
            'Muallim',
            'Bagan Datuk',
        ],
        'Perlis' => [
            'Perlis',
        ],
        'Pulau Pinang' => [
            'Timur Laut',
            'Seberang Perai Tengah',
            'Seberang Perai Utara',
            'Barat Daya',
            'Seberang Perai Selatan',
        ],
        'Sabah' => [
            'Kota Kinabalu',
            'Tawau',
            'Sandakan',
            'Lahad Datu',
            'Keningau',
            'Kinabatangan',
            'Semporna',
            'Papar',
            'Penampang',
            'Beluran',
            'Tuaran',
            'Ranau',
            'Kota Belud',
            'Kudat',
            'Kota Marudu',
            'Beaufort',
            'Kunak',
            'Tenom',
            'Putatan',
            'Pitas',
            'Tambunan',
            'Tongod',
            'Sipitang',
            'Nabawan',
            'Kuala Penyu',
            'Telupid',
            'Kalabakan',
        ],
        'Sarawak' => [
            'Kuching',
            'Miri',
            'Sibu',
            'Bintulu',
            'Serian',
            'Kota Samarahan',
            'Sri Aman',
            'Marudi',
            'Betong',
            'Sarikei',
            'Kapit',
            'Bau',
            'Limbang',
            'Saratok',
            'Mukah',
            'Simunjan',
            'Lawas',
            'Belaga',
            'Lundu',
            'Asajaya',
            'Daro',
            'Tatau',
            'Maradong',
            'Kanowit',
            'Lubok Antu',
            'Selangau',
            'Song',
            'Dalat',
            'Matu',
            'Julau',
            'Pakan',
            'Tanjung Manis',
            'Bukit Mabong',
            'Telang Usan',
            'Tebedu',
            'Subis',
            'Sebauh',
            'Beluru',
            'Kabong',
            'Gedong',
            'Siburan',
            'Pantu',
            'Lingga',
            'Sebuyau',
        ],
        'Selangor' => [
            'Petaling',
            'Hulu Langat',
            'Klang',
            'Gombak',
            'Kuala Langat',
            'Sepang',
            'Kuala Selangor',
            'Hulu Selangor',
            'Sabak Bernam',
        ],
        'Terengganu' => [
            'Kuala Terengganu',
            'Kemaman',
            'Dungun',
            'Besut',
            'Marang',
            'Hulu Terengganu',
            'Setiu',
            'Kuala Nerus',
        ],
        'Wilayah Persekutuan' => [
            'Kuala Lumpur',
            'Labuan',
            'Putrajaya',
        ],
    ];

    /**
     * Get districts for a specific state.
     */
    public static function getByState(string $state): array
    {
        return self::$districts[$state] ?? [];
    }

    /**
     * Get districts for a specific state as key-value pairs (name => name).
     */
    public static function getByStateOptions(string $state): array
    {
        $districts = self::getByState($state);

        return array_combine($districts, $districts);
    }

    /**
     * Get all districts.
     */
    public static function getAllDistricts(): array
    {
        $allDistricts = [];
        foreach (self::$districts as $districts) {
            $allDistricts = array_merge($allDistricts, $districts);
        }

        return $allDistricts;
    }

    /**
     * Find which state a district belongs to.
     */
    public static function getStateByDistrict(string $district): ?string
    {
        foreach (self::$districts as $state => $districts) {
            if (in_array($district, $districts)) {
                return $state;
            }
        }

        return null;
    }

    /**
     * Check if a district exists.
     */
    public static function exists(string $district): bool
    {
        return in_array($district, self::getAllDistricts());
    }
}
