<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LaboratoryTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'sample_type',
        'price',
    ];

    protected $casts = [
        'id' => 'string',
        'price' => 'decimal:2',
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

        // Generate UUID for new laboratory tests
        static::creating(function ($test) {
            if (empty($test->id) || $test->id === '0') {
                $test->id = (string) Str::uuid();
                \Log::info('Generated UUID for laboratory test: ' . $test->id);
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
     * Get the sample types as array.
     */
    public static function getSampleTypes()
    {
        return [
            'Blood' => 'Blood',
            'Urine' => 'Urine',
            'Saliva' => 'Saliva',
            'Stool' => 'Stool',
            'Sputum' => 'Sputum',
            'Swab' => 'Swab',
            'Tissue' => 'Tissue',
            'CSF' => 'CSF (Cerebrospinal Fluid)',
            'Synovial Fluid' => 'Synovial Fluid',
            'Pleural Fluid' => 'Pleural Fluid',
            'Peritoneal Fluid' => 'Peritoneal Fluid',
            'Other' => 'Other',
        ];
    }

    /**
     * Get the sample type badge HTML.
     */
    public function getSampleTypeBadgeAttribute()
    {
        $badges = [
            'Blood' => '<span class="badge bg-danger">Blood</span>',
            'Urine' => '<span class="badge bg-warning">Urine</span>',
            'Saliva' => '<span class="badge bg-info">Saliva</span>',
            'Stool' => '<span class="badge bg-secondary">Stool</span>',
            'Sputum' => '<span class="badge bg-dark">Sputum</span>',
            'Swab' => '<span class="badge bg-primary">Swab</span>',
            'Tissue' => '<span class="badge bg-success">Tissue</span>',
            'CSF' => '<span class="badge bg-purple">CSF</span>',
            'Synovial Fluid' => '<span class="badge bg-teal">Synovial Fluid</span>',
            'Pleural Fluid' => '<span class="badge bg-orange">Pleural Fluid</span>',
            'Peritoneal Fluid' => '<span class="badge bg-pink">Peritoneal Fluid</span>',
            'Other' => '<span class="badge bg-light text-dark">Other</span>',
        ];

        return $badges[$this->sample_type] ?? '<span class="badge bg-light text-dark">' . $this->sample_type . '</span>';
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute()
    {
        return '₦' . number_format($this->price, 2);
    }
}
