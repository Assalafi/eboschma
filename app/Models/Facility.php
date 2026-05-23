<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'lga',
        'ward',
        'type',
    ];

    /**
     * Get Borno State LGAs
     */
    public static function getBornoLGAs()
    {
        return [
            'Abadam', 'Askira/Uba', 'Bama', 'Bayo', 'Biu', 'Chibok', 'Damboa',
            'Dikwa', 'Gubio', 'Guzamala', 'Gwoza', 'Hawul', 'Jere', 'Kaga',
            'Kala/Balge', 'Konduga', 'Kukawa', 'Kwaya Kusar', 'Mafa', 'Magumeri',
            'Maiduguri', 'Marte', 'Mobbar', 'Monguno', 'Ngala', 'Nganzai',
            'Shani'
        ];
    }

    /**
     * Get facility types
     */
    public static function getFacilityTypes()
    {
        return [
            'Primary Health Care Center',
            'Secondary Health Care Center',
            'Primary',
            'Secondary',
            'Primary Care Center',
            'Secondary Care Center',
            'Primary Care',
            'Secondary Care',
        ];
    }

    /**
     * Get all wards for each LGA in Borno State
     * Returns an associative array with LGA names as keys and arrays of wards as values
     */
    public static function getBornoWards()
    {
        return [
            'Abadam' => ['Abadam', 'Arege', 'Gashagar', 'Jilkori', 'Kauram', 'Mallam Fatori', 'Muna Garage', 'Sokoto', 'Sumla', 'Wulgo'],
            'Askira/Uba' => ['Askira East', 'Askira West', 'Dille', 'Husara', 'Lassa', 'Mussa', 'Ngohi', 'Rubutu', 'Uba', 'Wamdeo', 'Zadawa'],
            'Bama' => ['Abbaram', 'Ajiri', 'Andara', 'Bulaburin', 'Gajiram', 'Goniri', 'Kasugula', 'Langawa', 'Lawanti', 'Malge', 'Soye', 'Wulari', 'Yuwe'],
            'Bayo' => ['Alagarno', 'Bayo', 'Briyel', 'Dawa', 'Gamadadi', 'Limanti', 'Teli', 'Wawa', 'Wimbi', 'Zara'],
            'Biu' => ['Biu', 'Buratai', 'Dadin Kowa', 'Dugja', 'Gur', 'Kenken', 'Mandaragirau', 'Miringa', 'Sulumthla', 'Yawi', 'Zarawuyaku'],
            'Chibok' => ['Chibok', 'Gatamarwa', 'Kautikari', 'Korongilim', 'Mbalala', 'Mboa-Goka', 'Pemi', 'Piyami', 'Shikarkir', 'Wumtaku'],
            'Damboa' => ['Ajgin', 'Azur', 'Damboa', 'Gasi', 'Gumsuri', 'Jangurori', 'Kaigamari', 'Koa', 'Korede', 'Mulgwe', 'Ngudoram', 'Wawa'],
            'Dikwa' => ['Dikwa', 'Gajibo', 'Kala Balge', 'Logomani', 'Muliye', 'Sogoma'],
            'Gubio' => ['Daban Masara', 'Dabira', 'Goni Usmanti', 'Gubio', 'Kareram', 'Zowo'],
            'Guzamala' => ['Buk', 'Guzamala', 'Gudumbali', 'Kingarwa', 'Wajiro'],
            'Gwoza' => ['Ashigashiya', 'Bokko', 'Dure', 'Gavva', 'Gwoza', 'Hambagda', 'Izge', 'Johode', 'Kirawa', 'Limankara', 'Ngoshe', 'Pulka'],
            'Hawul' => ['Azare', 'Gwanzang', 'Hawul', 'Hizhi', 'Kida', 'Marama', 'Puba', 'Sakwa', 'Shaffa', 'Widimari'],
            'Jere' => ['Addamari', 'Alau', 'Dusuman', 'Gongulong', 'Maimusari', 'Mashamari', 'Old Maiduguri', 'Shuwari'],
            'Kaga' => ['Benisheikh', 'Borgozo', 'Dogoma', 'Doro', 'Gajiram', 'Guzamala', 'Karagawaru', 'Kaska', 'Mainok', 'Marguba', 'Ngamdu', 'Tobolo', 'Wassaram', 'Wulgo'],
            'Kala/Balge' => ['Gasi', 'Kumalia', 'Kurnawa', 'Logomani', 'Rann', 'Sigal', 'Sigel', 'Tarmuwa', 'Wulgo', 'Wurge'],
            'Konduga' => ['Auno', 'Dalori', 'Jakana', 'Kawuri', 'Kelumiri', 'Konduga', 'Maiwa', 'Malari', 'Masba', 'Ngom', 'Njimiya', 'Sojiri'],
            'Kukawa' => ['Baga', 'Bundur', 'Cross Kauwa', 'Doro Baga', 'Doro Gowon', 'Duguri', 'Kauwa', 'Kekeno', 'Kukawa', 'Yoyo'],
            'Kwaya Kusar' => ['Bille', 'Guwal', 'Kofa', 'Kwaya Kusar', 'Wagga', 'Wawa'],
            'Mafa' => ['Ajari', 'Gawa', 'Limanti', 'Mafa', 'Masu', 'Mujilive', 'Tamsu Ngamdua', 'Tamsu Ngala', 'Uda'],
            'Magumeri' => ['Borumdula', 'Furram', 'Gumna', 'Hoyo', 'Kalizoram', 'Kareto', 'Magumeri', 'Ngamma'],
            'Maiduguri' => ['Bolori I', 'Bolori II', 'Bulablin', 'Fezzan', 'Gamboru Liberty', 'Gwange I', 'Gwange II', 'Gwange III', 'Hausari', 'Lamisula', 'Limanti', 'Mafoni', 'Maisandari', 'Shehuri North', 'Shehuri South'],
            'Marte' => ['Alagarno', 'Kabulawa', 'Kirenowa', 'Kuda', 'Marte', 'Mawulli', 'Muna Garage', 'Musune', 'Ngala', 'Njine', 'Zaga'],
            'Mobbar' => ['Asaga', 'Bogum', 'Chamba', 'Dabar', 'Damasak', 'Duji', 'Garunda', 'Karamga', 'Kareto', 'Layi', 'Zanna Umorti'],
            'Monguno' => ['Gabchari', 'Jere', 'Kaguram', 'Kumalia', 'Mandala', 'Mintar', 'Monguno', 'Ngudoram', 'Wulo', 'Yele'],
            'Ngala' => ['Fuye', 'Gamboru', 'Gamboru Ngala', 'Gombole', 'Jere', 'Kaigama', 'Kuda', 'Ndufu', 'Ngala', 'Shuwari'],
            'Nganzai' => ['Alarge', 'Damakuli', 'Gadai', 'Gajiram', 'Jabulam', 'Kekeno', 'Kuda', 'Mairari', 'Miye', 'Nganzai'],
            'Shani' => ['Bargu/Burashika', 'Buma', 'Gasi', 'Gwalasho', 'Gwaskara', 'Kombo', 'Kubo', 'Kwaba', 'Shani', 'Walama'],
        ];
    }

    /**
     * Get wards for a specific LGA
     * 
     * @param string $lga
     * @return array
     */
    public static function getWardsByLga($lga)
    {
        $wards = self::getBornoWards();
        return $wards[$lga] ?? [];
    }

    /**
     * Scope to filter by LGA
     */
    public function scopeByLga($query, $lga)
    {
        return $query->where('lga', $lga);
    }

    /**
     * Scope to filter by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get beneficiaries associated with this facility
     */
    public function beneficiaries()
    {
        return $this->hasMany(Beneficiary::class);
    }

    /**
     * Get spouses associated with this facility
     */
    public function spouses()
    {
        return $this->hasMany(Spouse::class);
    }

    /**
     * Get children associated with this facility
     */
    public function children()
    {
        return $this->hasMany(Child::class);
    }

    /**
     * Get the secondary services provided by this facility
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'facility_has_services', 'facility_id', 'service_id')
                    ->where('type', 'Secondary')
                    ->withTimestamps();
    }

    /**
     * Get the facility services (new system)
     */
    public function facilityServices()
    {
        return $this->hasMany(FacilityService::class);
    }

    /**
     * Get wards belonging to this facility
     */
    public function wards()
    {
        return $this->hasMany(Ward::class);
    }

    /**
     * Get staff associated with this facility
     */
    public function staff()
    {
        return $this->belongsToMany(Staff::class, 'facility_staff', 'facility_id', 'staff_id')->withTimestamps();
    }
}
