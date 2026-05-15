<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Beneficiary extends Model
{
    use HasFactory;
    
    /**
     * Mass assignable attributes.
     * NOTE: boschma_no and sequence_number CAN be set during creation,
     * but should NEVER be updated after initial assignment (enforced in controllers).
     */
    protected $fillable = [
        'facility_id',
        'alt_facility_id',
        'program_id',
        'boschma_no',
        'sequence_number',
        'fullname',
        'date_of_birth',
        'gender',
        'phone_no',
        'email',
        'contact_address',
        'city',
        'state',
        'country',
        'id_type',
        'id_no',
        'nin',
        'photo',
        'signature',
        'status',
        'has_spouse',
        'number_of_children',
        'remarks',
        'place_of_birth',
        'lga',
        'nationality',
        'marital_status',
        'ethnicity',
        'religion',
        'occupation',
        'dp_no',
        'place_of_work',
        'date_of_employment',
        'date_of_retirement',
        'category',
        'signature_date',
        'created_by',
        'submitted_by',
        'updated_by',
        'created_at' // Allow mobile app to set original creation date
    ];

    /**
     * Get the facility associated with the beneficiary.
     */
    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    /**
     * Get the alternative facility associated with the beneficiary.
     */
    public function altFacility()
    {
        return $this->belongsTo(Facility::class, 'alt_facility_id');
    }

    /**
     * Get the program associated with the beneficiary.
     */
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Get the spouse associated with the beneficiary.
     */
    public function spouse()
    {
        return $this->hasOne(Spouse::class);
    }

    /**
     * Get the children for the beneficiary.
     */
    public function children()
    {
        return $this->hasMany(Child::class);
    }

    /**
     * Get the contributions for the beneficiary (linked by dp_no).
     */
    public function contributions()
    {
        return $this->hasMany(Contribution::class, 'dp_no', 'dp_no');
    }
    
    /**
     * Get all dependents (spouse + children)
     */
    public function dependents()
    {
        $dependents = [];
        
        if ($this->spouse) {
            $dependents[] = $this->spouse;
        }
        
        return array_merge($dependents, $this->children->all());
    }
    
    /**
     * Get the staff member who created this beneficiary.
     */
    public function creator()
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }
    
    /**
     * Get the staff member who submitted/finalized this beneficiary.
     */
    public function submitter()
    {
        return $this->belongsTo(Staff::class, 'submitted_by');
    }
    
    /**
     * Get the staff member who last updated this beneficiary.
     */
    public function updater()
    {
        return $this->belongsTo(Staff::class, 'updated_by');
    }
    
    /**
     * Get the complete list of ethnicity options with Borno State focus
     */
    public static function getEthnicityOptions()
    {
        return [
            // Major Groups - Borno State
            'Kanuri' => 'Kanuri',
            'Shuwa Arabs' => 'Shuwa Arabs',
            'Marghi' => 'Marghi',
            'Babur' => 'Babur',
            'Bura' => 'Bura',
            'Hausa' => 'Hausa',
            'Fulani' => 'Fulani',
            'Gamergu' => 'Gamergu',
            'Mandara' => 'Mandara',
            'Glavda' => 'Glavda',
            'Guduf-Gava' => 'Guduf-Gava',
            'Matakam (Mafa)' => 'Matakam (Mafa)',
            'Waga (Waha)' => 'Waga (Waha)',
            'Dghwede (Dguede)' => 'Dghwede (Dguede)',
            'Tera' => 'Tera',
            'Higi (Kamwe)' => 'Higi (Kamwe)',
            'Mandar' => 'Mandar',
            'Ngoshe' => 'Ngoshe',
            'Ndhang' => 'Ndhang',
            'Pulka' => 'Pulka',
            'Chibok (Kibaku)' => 'Chibok (Kibaku)',
            'Kapsiki' => 'Kapsiki',
            'Wula' => 'Wula',
            'Daba' => 'Daba',
            'Bokko (Boko)' => 'Bokko (Boko)',
            'Zul' => 'Zul',
            'Tiv' => 'Tiv',
            'Mandara Hausa' => 'Mandara Hausa',
            'Kwaya' => 'Kwaya',
            'Ngizim' => 'Ngizim',
            'Woji (Uji)' => 'Woji (Uji)',
            'Lamang (Hildi)' => 'Lamang (Hildi)',
            'Tera-Bura subgroup' => 'Tera-Bura subgroup',
            'Kilba' => 'Kilba',
            'Wandala' => 'Wandala',
            'Podoko' => 'Podoko',
            'Sukur' => 'Sukur',
            'Gava' => 'Gava',
            'Dzang' => 'Dzang',
            'Hona' => 'Hona',
            'Jara' => 'Jara',
            'Kirfa' => 'Kirfa',
            'Pabir (Babur variant)' => 'Pabir (Babur variant)',
            'Gulani' => 'Gulani',
            'Balewa' => 'Balewa',
            'Mulgwe' => 'Mulgwe',
            'Ngwaba' => 'Ngwaba',
            'Nzangi' => 'Nzangi',
            'Gude' => 'Gude',
            'Mandaya' => 'Mandaya',
            'Mbula' => 'Mbula',
            'Tangle' => 'Tangle',
            'Longuda' => 'Longuda',
            'Kilba-Higi subgroup' => 'Kilba-Higi subgroup',
            'Wurma' => 'Wurma',
            'Kubo' => 'Kubo',
            'Ngamo' => 'Ngamo',
            'Bade' => 'Bade',
            'Yedina (Buduma)' => 'Yedina (Buduma)',
            'Koyam' => 'Koyam',

            // Other Major Nigerian Ethnic Groups (for completeness)
            'Yoruba' => 'Yoruba',
            'Igbo' => 'Igbo',
            'Nupe' => 'Nupe',
            'Gbagyi' => 'Gbagyi',
            'Jukun' => 'Jukun',
            'Other' => 'Other',
        ];
    }

    /**
     * Get overall enrollment statistics.
     *
     * @return array
     */
    public static function getEnrollmentStats(): array
    {
        $totalBeneficiaries = self::where('status', '!=', 'draft')->count();
        $totalSpouses = Spouse::count();
        $totalChildren = Child::count();
        $totalEnrollments = $totalBeneficiaries + $totalSpouses + $totalChildren;

        return [
            'total_beneficiaries' => $totalBeneficiaries,
            'total_spouses' => $totalSpouses,
            'total_children' => $totalChildren,
            'total_enrollments' => $totalEnrollments,
        ];
    }

    /**
     * Get self enrollment count.
     * Self enrollments are those where created_by is null and created_at > 2026-01-08
     *
     * @return int
     */
    public static function getSelfEnrollmentCount(): int
    {
        return self::whereNull('created_by')
            ->where('created_at', '>', '2026-01-08')
            ->where('status', '!=', 'draft')
            ->count();
    }

    /**
     * Get top facilities by enrollment count.
     *
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public static function getTopFacilities(int $limit = 10)
    {
        // Use efficient SQL aggregation instead of loading all records into memory
        $results = DB::table('facilities')
            ->leftJoin('beneficiaries', function ($join) {
                $join->on('facilities.id', '=', 'beneficiaries.facility_id')
                     ->where('beneficiaries.status', '!=', 'draft');
            })
            ->leftJoin('spouses', 'beneficiaries.id', '=', 'spouses.beneficiary_id')
            ->leftJoin('children', 'beneficiaries.id', '=', 'children.beneficiary_id')
            ->select(
                'facilities.id',
                'facilities.name',
                DB::raw('COUNT(DISTINCT beneficiaries.id) as beneficiaries_count'),
                DB::raw('COUNT(DISTINCT spouses.id) as spouses_count'),
                DB::raw('COUNT(DISTINCT children.id) as children_count'),
                DB::raw('(COUNT(DISTINCT beneficiaries.id) + COUNT(DISTINCT spouses.id) + COUNT(DISTINCT children.id)) as enrollments_count')
            )
            ->groupBy('facilities.id', 'facilities.name')
            ->havingRaw('(COUNT(DISTINCT beneficiaries.id) + COUNT(DISTINCT spouses.id) + COUNT(DISTINCT children.id)) > 0')
            ->orderByDesc('enrollments_count')
            ->limit($limit)
            ->get();

        return $results;
    }

    /**
     * Get enrollment statistics grouped by program.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getProgramStats()
    {
        // Use efficient SQL aggregation instead of N+1 queries per program
        $results = DB::table('programs')
            ->leftJoin('beneficiaries', function ($join) {
                $join->on('programs.id', '=', 'beneficiaries.program_id')
                     ->where('beneficiaries.status', '!=', 'draft');
            })
            ->leftJoin('spouses', 'beneficiaries.id', '=', 'spouses.beneficiary_id')
            ->leftJoin('children', 'beneficiaries.id', '=', 'children.beneficiary_id')
            ->select(
                'programs.name as program_name',
                DB::raw('COUNT(DISTINCT beneficiaries.id) as beneficiaries'),
                DB::raw('COUNT(DISTINCT spouses.id) as spouses'),
                DB::raw('COUNT(DISTINCT children.id) as children'),
                DB::raw('(COUNT(DISTINCT beneficiaries.id) + COUNT(DISTINCT spouses.id) + COUNT(DISTINCT children.id)) as total')
            )
            ->groupBy('programs.id', 'programs.name')
            ->havingRaw('(COUNT(DISTINCT beneficiaries.id) + COUNT(DISTINCT spouses.id) + COUNT(DISTINCT children.id)) > 0')
            ->orderBy('programs.name')
            ->get();

        return $results;
    }

    /**
     * Get monthly enrollment statistics.
     *
     * @return array
     */
    public static function getMonthlyStats(): array
    {
        $thisMonthStart = now()->startOfMonth();
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        // This month enrollments
        $thisMonthBeneficiaries = self::where('created_at', '>=', $thisMonthStart)
            ->where('status', '!=', 'draft')
            ->count();
        $thisMonthSpouses = Spouse::where('created_at', '>=', $thisMonthStart)->count();
        $thisMonthChildren = Child::where('created_at', '>=', $thisMonthStart)->count();
        $thisMonthTotal = $thisMonthBeneficiaries + $thisMonthSpouses + $thisMonthChildren;

        // Last month enrollments
        $lastMonthBeneficiaries = self::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->where('status', '!=', 'draft')
            ->count();
        $lastMonthSpouses = Spouse::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $lastMonthChildren = Child::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $lastMonthTotal = $lastMonthBeneficiaries + $lastMonthSpouses + $lastMonthChildren;

        return [
            'this_month' => $thisMonthTotal,
            'last_month' => $lastMonthTotal,
            'this_month_beneficiaries' => $thisMonthBeneficiaries,
            'this_month_spouses' => $thisMonthSpouses,
            'this_month_children' => $thisMonthChildren,
            'last_month_beneficiaries' => $lastMonthBeneficiaries,
            'last_month_spouses' => $lastMonthSpouses,
            'last_month_children' => $lastMonthChildren,
        ];
    }
}
