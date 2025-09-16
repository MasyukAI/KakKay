<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Nnjeim\World\Models\State as WorldState;
use Sushi\Sushi;

class District extends Model
{
    use Sushi;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'state_id',
        'name',
        'code_3',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'state_id' => 'string',
        'id' => 'string',
    ];

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Get the district data for Sushi.
     */
    public function getRows(): array
    {
        // All district names are now Title Case (e.g., 'Kota Bharu')
        return [
            ['id' => '0900', 'state_id' => '9', 'name' => 'Perlis', 'code_3' => 'PER'],
            ['id' => '0201', 'state_id' => '2', 'name' => 'Kota Setar', 'code_3' => 'KSE'],
            ['id' => '0202', 'state_id' => '2', 'name' => 'Kubang Pasu', 'code_3' => 'KPA'],
            ['id' => '0203', 'state_id' => '2', 'name' => 'Padang Terap', 'code_3' => 'PTE'],
            ['id' => '0204', 'state_id' => '2', 'name' => 'Langkawi', 'code_3' => 'LAN'],
            ['id' => '0205', 'state_id' => '2', 'name' => 'Kuala Muda', 'code_3' => 'KMU'],
            ['id' => '0206', 'state_id' => '2', 'name' => 'Yan', 'code_3' => 'YAN'],
            ['id' => '0207', 'state_id' => '2', 'name' => 'Sik', 'code_3' => 'SIK'],
            ['id' => '0208', 'state_id' => '2', 'name' => 'Baling', 'code_3' => 'BAL'],
            ['id' => '0209', 'state_id' => '2', 'name' => 'Kulim', 'code_3' => 'KUL'],
            ['id' => '0210', 'state_id' => '2', 'name' => 'Bandar Baharu', 'code_3' => 'BBA'],
            ['id' => '0211', 'state_id' => '2', 'name' => 'Pendang', 'code_3' => 'PEN'],
            ['id' => '0212', 'state_id' => '2', 'name' => 'Pokok Sena', 'code_3' => 'PSE'],
            ['id' => '0301', 'state_id' => '3', 'name' => 'Bachok', 'code_3' => 'BAC'],
            ['id' => '0302', 'state_id' => '3', 'name' => 'Kota Bharu', 'code_3' => 'KBH'],
            ['id' => '0303', 'state_id' => '3', 'name' => 'Machang', 'code_3' => 'MAC'],
            ['id' => '0304', 'state_id' => '3', 'name' => 'Pasir Mas', 'code_3' => 'PMA'],
            ['id' => '0305', 'state_id' => '3', 'name' => 'Pasir Puteh', 'code_3' => 'PPU'],
            ['id' => '0306', 'state_id' => '3', 'name' => 'Tanah Merah', 'code_3' => 'TME'],
            ['id' => '0307', 'state_id' => '3', 'name' => 'Tumpat', 'code_3' => 'TUM'],
            ['id' => '0308', 'state_id' => '3', 'name' => 'Gua Musang', 'code_3' => 'GMU'],
            ['id' => '0310', 'state_id' => '3', 'name' => 'Kuala Krai', 'code_3' => 'KKR'],
            ['id' => '0311', 'state_id' => '3', 'name' => 'Jeli', 'code_3' => 'JEL'],
            ['id' => '0312', 'state_id' => '3', 'name' => 'Kecil Lojing', 'code_3' => 'KLO'],
            ['id' => '1101', 'state_id' => '11', 'name' => 'Besut', 'code_3' => 'BES'],
            ['id' => '1102', 'state_id' => '11', 'name' => 'Dungun', 'code_3' => 'DUN'],
            ['id' => '1103', 'state_id' => '11', 'name' => 'Kemaman', 'code_3' => 'KEM'],
            ['id' => '1104', 'state_id' => '11', 'name' => 'Kuala Terengganu', 'code_3' => 'KTE'],
            ['id' => '1105', 'state_id' => '11', 'name' => 'Hulu Terengganu', 'code_3' => 'HTE'],
            ['id' => '1106', 'state_id' => '11', 'name' => 'Marang', 'code_3' => 'MAR'],
            ['id' => '1107', 'state_id' => '11', 'name' => 'Setiu', 'code_3' => 'SET'],
            ['id' => '1108', 'state_id' => '11', 'name' => 'Kuala Nerus', 'code_3' => 'KNE'],
            ['id' => '0701', 'state_id' => '7', 'name' => 'Seberang Perai Tengah', 'code_3' => 'SPT'],
            ['id' => '0702', 'state_id' => '7', 'name' => 'Seberang Perai Utara', 'code_3' => 'SPU'],
            ['id' => '0703', 'state_id' => '7', 'name' => 'Seberang Perai Selatan', 'code_3' => 'SPS'],
            ['id' => '0704', 'state_id' => '7', 'name' => 'Timor Laut', 'code_3' => 'TLA'],
            ['id' => '0705', 'state_id' => '7', 'name' => 'Barat Daya', 'code_3' => 'BDA'],
            ['id' => '0801', 'state_id' => '8', 'name' => 'Batang Padang', 'code_3' => 'BPA'],
            ['id' => '0802', 'state_id' => '8', 'name' => 'Manjung', 'code_3' => 'MAN'],
            ['id' => '0803', 'state_id' => '8', 'name' => 'Kinta', 'code_3' => 'KIN'],
            ['id' => '0804', 'state_id' => '8', 'name' => 'Kerian', 'code_3' => 'KER'],
            ['id' => '0805', 'state_id' => '8', 'name' => 'Kuala Kangsar', 'code_3' => 'KKA'],
            ['id' => '0806', 'state_id' => '8', 'name' => 'Larut & Matang', 'code_3' => 'LDM'],
            ['id' => '0807', 'state_id' => '8', 'name' => 'Hilir Perak', 'code_3' => 'HPE'],
            ['id' => '0808', 'state_id' => '8', 'name' => 'Hulu Perak', 'code_3' => 'HPR'],
            ['id' => '0809', 'state_id' => '8', 'name' => 'Selama', 'code_3' => 'SEL'],
            ['id' => '0810', 'state_id' => '8', 'name' => 'Perak Tengah', 'code_3' => 'PTE'],
            ['id' => '0811', 'state_id' => '8', 'name' => 'Kampar', 'code_3' => 'KAM'],
            ['id' => '0601', 'state_id' => '6', 'name' => 'Bentong', 'code_3' => 'BEN'],
            ['id' => '0602', 'state_id' => '6', 'name' => 'Cameron Highlands', 'code_3' => 'CHI'],
            ['id' => '0603', 'state_id' => '6', 'name' => 'Jerantut', 'code_3' => 'JER'],
            ['id' => '0604', 'state_id' => '6', 'name' => 'Kuantan', 'code_3' => 'KUA'],
            ['id' => '0605', 'state_id' => '6', 'name' => 'Lipis', 'code_3' => 'LIP'],
            ['id' => '0606', 'state_id' => '6', 'name' => 'Pekan', 'code_3' => 'PEK'],
            ['id' => '0607', 'state_id' => '6', 'name' => 'Raub', 'code_3' => 'RAU'],
            ['id' => '0608', 'state_id' => '6', 'name' => 'Temerloh', 'code_3' => 'TER'],
            ['id' => '0609', 'state_id' => '6', 'name' => 'Rompin', 'code_3' => 'ROM'],
            ['id' => '0610', 'state_id' => '6', 'name' => 'Maran', 'code_3' => 'MAR'],
            ['id' => '0611', 'state_id' => '6', 'name' => 'Bera', 'code_3' => 'BER'],
            ['id' => '1001', 'state_id' => '10', 'name' => 'Klang', 'code_3' => 'KLG'],
            ['id' => '1002', 'state_id' => '10', 'name' => 'Kuala Langat', 'code_3' => 'KLN'],
            ['id' => '1004', 'state_id' => '10', 'name' => 'Kuala Selangor', 'code_3' => 'KSE'],
            ['id' => '1005', 'state_id' => '10', 'name' => 'Sabak Bernam', 'code_3' => 'SBE'],
            ['id' => '1006', 'state_id' => '10', 'name' => 'Ulu Langat', 'code_3' => 'ULA'],
            ['id' => '1007', 'state_id' => '10', 'name' => 'Ulu Selangor', 'code_3' => 'USE'],
            ['id' => '1008', 'state_id' => '10', 'name' => 'Petaling', 'code_3' => 'PET'],
            ['id' => '1009', 'state_id' => '10', 'name' => 'Gombak', 'code_3' => 'GOM'],
            ['id' => '1010', 'state_id' => '10', 'name' => 'Sepang', 'code_3' => 'SEP'],
            ['id' => '1400', 'state_id' => '14', 'name' => 'W. P. Kuala Lumpur', 'code_3' => 'WPK'],
            ['id' => '1601', 'state_id' => '16', 'name' => 'W. P. Putrajaya', 'code_3' => 'WPP'],
            ['id' => '0501', 'state_id' => '5', 'name' => 'Jelebu', 'code_3' => 'JEL'],
            ['id' => '0502', 'state_id' => '5', 'name' => 'Kuala Pilah', 'code_3' => 'KPI'],
            ['id' => '0503', 'state_id' => '5', 'name' => 'Port Dickson', 'code_3' => 'PDI'],
            ['id' => '0504', 'state_id' => '5', 'name' => 'Rembau', 'code_3' => 'REM'],
            ['id' => '0505', 'state_id' => '5', 'name' => 'Seremban', 'code_3' => 'SER'],
            ['id' => '0506', 'state_id' => '5', 'name' => 'Tampin', 'code_3' => 'TAM'],
            ['id' => '0507', 'state_id' => '5', 'name' => 'Jempol', 'code_3' => 'JEM'],
            ['id' => '0401', 'state_id' => '4', 'name' => 'Melaka Tengah', 'code_3' => 'MTE'],
            ['id' => '0402', 'state_id' => '4', 'name' => 'Jasin', 'code_3' => 'JAS'],
            ['id' => '0403', 'state_id' => '4', 'name' => 'Alor Gajah', 'code_3' => 'AGA'],
            ['id' => '0101', 'state_id' => '1', 'name' => 'Batu Pahat', 'code_3' => 'BPA'],
            ['id' => '0102', 'state_id' => '1', 'name' => 'Johor Bahru', 'code_3' => 'JBA'],
            ['id' => '0103', 'state_id' => '1', 'name' => 'Kluang', 'code_3' => 'KLU'],
            ['id' => '0104', 'state_id' => '1', 'name' => 'Kota Tinggi', 'code_3' => 'KTI'],
            ['id' => '0105', 'state_id' => '1', 'name' => 'Mersing', 'code_3' => 'MER'],
            ['id' => '0106', 'state_id' => '1', 'name' => 'Muar', 'code_3' => 'MUA'],
            ['id' => '0107', 'state_id' => '1', 'name' => 'Pontian', 'code_3' => 'PON'],
            ['id' => '0108', 'state_id' => '1', 'name' => 'Segamat', 'code_3' => 'SEG'],
            ['id' => '0109', 'state_id' => '1', 'name' => 'Kulaijaya', 'code_3' => 'KUL'],
            ['id' => '0110', 'state_id' => '1', 'name' => 'Ledang', 'code_3' => 'LED'],
            ['id' => '1500', 'state_id' => '15', 'name' => 'W. P. Labuan', 'code_3' => 'WPL'],
            ['id' => '1201', 'state_id' => '12', 'name' => 'Kota Kinabalu', 'code_3' => 'KKI'],
            ['id' => '1202', 'state_id' => '12', 'name' => 'Papar', 'code_3' => 'PAP'],
            ['id' => '1203', 'state_id' => '12', 'name' => 'Kota Belud', 'code_3' => 'KBE'],
            ['id' => '1204', 'state_id' => '12', 'name' => 'Tuaran', 'code_3' => 'TUA'],
            ['id' => '1205', 'state_id' => '12', 'name' => 'Kudat', 'code_3' => 'KUD'],
            ['id' => '1206', 'state_id' => '12', 'name' => 'Ranau', 'code_3' => 'RAN'],
            ['id' => '1207', 'state_id' => '12', 'name' => 'Sandakan', 'code_3' => 'SAN'],
            ['id' => '1208', 'state_id' => '12', 'name' => 'Labuk & Sugut', 'code_3' => 'LDS'],
            ['id' => '1209', 'state_id' => '12', 'name' => 'Kinabatangan', 'code_3' => 'KIN'],
            ['id' => '1210', 'state_id' => '12', 'name' => 'Tawau', 'code_3' => 'TAW'],
            ['id' => '1211', 'state_id' => '12', 'name' => 'Lahad Datu', 'code_3' => 'LDA'],
            ['id' => '1212', 'state_id' => '12', 'name' => 'Semporna', 'code_3' => 'SEM'],
            ['id' => '1213', 'state_id' => '12', 'name' => 'Keningau', 'code_3' => 'KEN'],
            ['id' => '1214', 'state_id' => '12', 'name' => 'Tambunan', 'code_3' => 'TAM'],
            ['id' => '1215', 'state_id' => '12', 'name' => 'Pensiangan', 'code_3' => 'PEN'],
            ['id' => '1216', 'state_id' => '12', 'name' => 'Tenom', 'code_3' => 'TEN'],
            ['id' => '1217', 'state_id' => '12', 'name' => 'Beaufort', 'code_3' => 'BEA'],
            ['id' => '1218', 'state_id' => '12', 'name' => 'Kuala Penyu', 'code_3' => 'KPE'],
            ['id' => '1219', 'state_id' => '12', 'name' => 'Sipitang', 'code_3' => 'SIP'],
            ['id' => '1221', 'state_id' => '12', 'name' => 'Penampang', 'code_3' => 'PEN'],
            ['id' => '1222', 'state_id' => '12', 'name' => 'Kota Marudu', 'code_3' => 'KMA'],
            ['id' => '1223', 'state_id' => '12', 'name' => 'Pitas', 'code_3' => 'PTS'],
            ['id' => '1224', 'state_id' => '12', 'name' => 'Kunak', 'code_3' => 'KUN'],
            ['id' => '1225', 'state_id' => '12', 'name' => 'Tongod', 'code_3' => 'TON'],
            ['id' => '1226', 'state_id' => '12', 'name' => 'Putatan', 'code_3' => 'PUT'],
            ['id' => '1301', 'state_id' => '13', 'name' => 'Kuching', 'code_3' => 'KUC'],
            ['id' => '1302', 'state_id' => '13', 'name' => 'Sri Aman', 'code_3' => 'SAM'],
            ['id' => '1303', 'state_id' => '13', 'name' => 'Sibu', 'code_3' => 'SIB'],
            ['id' => '1304', 'state_id' => '13', 'name' => 'Miri', 'code_3' => 'MIR'],
            ['id' => '1305', 'state_id' => '13', 'name' => 'Limbang', 'code_3' => 'LIM'],
            ['id' => '1306', 'state_id' => '13', 'name' => 'Sarikei', 'code_3' => 'SAR'],
            ['id' => '1307', 'state_id' => '13', 'name' => 'Kapit', 'code_3' => 'KAP'],
            ['id' => '1308', 'state_id' => '13', 'name' => 'Samarahan', 'code_3' => 'SAM'],
            ['id' => '1309', 'state_id' => '13', 'name' => 'Bintulu', 'code_3' => 'BIN'],
            ['id' => '1310', 'state_id' => '13', 'name' => 'Mukah', 'code_3' => 'MUK'],
            ['id' => '1311', 'state_id' => '13', 'name' => 'Betong', 'code_3' => 'BET'],
        ];
    }

    /**
     * Relationship: District belongs to a state.
     */
    public function state()
    {
        return $this->belongsTo(WorldState::class, 'state_id', 'id');
    }

    /**
     * Scope a query to only include districts for a specific state.
     */
    public function scopeForState($query, $stateId)
    {
        return $query->where('state_id', $stateId);
    }

    /**
     * Get districts by state ID.
     */
    public static function getByState($stateId)
    {
        return static::forState($stateId)->orderBy('name')->get();
    }
}
