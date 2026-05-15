<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class IcdCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
        'category',
    ];

    protected $casts = [
        'id' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Generate UUID for new ICD codes
        static::creating(function ($icdCode) {
            if (empty($icdCode->id) || $icdCode->id === '0') {
                $icdCode->id = (string) Str::uuid();
                \Log::info('Generated UUID for ICD code: ' . $icdCode->id);
            }
        });
    }

    /**
     * Get the route key for the model.
     * Using id for routing
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Get the ICD categories as array.
     */
    public static function getCategories()
    {
        return [
            'A00-B99' => 'Certain infectious and parasitic diseases',
            'C00-D49' => 'Neoplasms',
            'D50-D89' => 'Diseases of the blood and blood-forming organs',
            'E00-E89' => 'Endocrine, nutritional and metabolic diseases',
            'F01-F99' => 'Mental and behavioural disorders',
            'G00-G99' => 'Diseases of the nervous system',
            'H00-H59' => 'Diseases of the eye and adnexa',
            'H60-H95' => 'Diseases of the ear and mastoid process',
            'I00-I99' => 'Diseases of the circulatory system',
            'J00-J99' => 'Diseases of the respiratory system',
            'K00-K93' => 'Diseases of the digestive system',
            'L00-L99' => 'Diseases of the skin and subcutaneous tissue',
            'M00-M99' => 'Diseases of the musculoskeletal system and connective tissue',
            'N00-N99' => 'Diseases of the genitourinary system',
            'O00-O9A' => 'Pregnancy, childbirth and the puerperium',
            'P00-P96' => 'Certain conditions originating in the perinatal period',
            'Q00-Q99' => 'Congenital malformations, deformations and chromosomal abnormalities',
            'R00-R99' => 'Symptoms, signs and abnormal clinical findings',
            'S00-T98' => 'Injury, poisoning and certain other consequences',
            'U00-U99' => 'Codes for special purposes',
            'V01-Y99' => 'External causes of morbidity and mortality',
            'Z00-Z99' => 'Factors influencing health status',
        ];
    }

    /**
     * Get the category badge HTML.
     */
    public function getCategoryBadgeAttribute()
    {
        $badges = [
            'A00-B99' => '<span class="badge bg-danger">A00-B99</span>',
            'C00-D49' => '<span class="badge bg-dark">C00-D49</span>',
            'D50-D89' => '<span class="badge bg-warning">D50-D89</span>',
            'E00-E89' => '<span class="badge bg-info">E00-E89</span>',
            'F01-F99' => '<span class="badge bg-purple">F01-F99</span>',
            'G00-G99' => '<span class="badge bg-primary">G00-G99</span>',
            'H00-H59' => '<span class="badge bg-success">H00-H59</span>',
            'H60-H95' => '<span class="badge bg-teal">H60-H95</span>',
            'I00-I99' => '<span class="badge bg-danger">I00-I99</span>',
            'J00-J99' => '<span class="badge bg-secondary">J00-J99</span>',
            'K00-K93' => '<span class="badge bg-orange">K00-K93</span>',
            'L00-L99' => '<span class="badge bg-pink">L00-L99</span>',
            'M00-M99' => '<span class="badge bg-indigo">M00-M99</span>',
            'N00-N99' => '<span class="badge bg-brown">N00-N99</span>',
            'O00-O9A' => '<span class="badge bg-purple">O00-O9A</span>',
            'P00-P96' => '<span class="badge bg-cyan">P00-P96</span>',
            'Q00-Q99' => '<span class="badge bg-gray">Q00-Q99</span>',
            'R00-R99' => '<span class="badge bg-light text-dark">R00-R99</span>',
            'S00-T98' => '<span class="badge bg-danger">S00-T98</span>',
            'U00-U99' => '<span class="badge bg-dark">U00-U99</span>',
            'V01-Y99' => '<span class="badge bg-secondary">V01-Y99</span>',
            'Z00-Z99' => '<span class="badge bg-success">Z00-Z99</span>',
        ];

        return $badges[$this->category] ?? '<span class="badge bg-light text-dark">' . $this->category . '</span>';
    }

    /**
     * Get the formatted code with description.
     */
    public function getCodeWithDescriptionAttribute()
    {
        return $this->code . ' - ' . $this->description;
    }
}
