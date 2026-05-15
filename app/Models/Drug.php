<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Drug extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'dosage_form',
        'strength',
        'unit',
        'unit_price',
        'facility_id',
        'status'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) \Illuminate\Support\Str::uuid();
            }
            
            // Check for existing drug with same specifications
            $existingDrug = self::where('name', $model->name)
                ->where('dosage_form', $model->dosage_form)
                ->where('strength', $model->strength)
                ->where('unit', $model->unit)
                ->where('unit_price', $model->unit_price)
                ->first();
                
            if ($existingDrug) {
                throw new \Exception("A drug with the same name, dosage form, strength, unit, and price already exists.");
            }
        });
    }

    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    // Relationships
    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function stocks()
    {
        return $this->hasMany(DrugStock::class);
    }

    public function drugStocks()
    {
        return $this->hasMany(DrugStock::class);
    }

    public function stockRequests()
    {
        return $this->hasMany(DrugStockRequest::class);
    }

    public function dispensations()
    {
        return $this->hasManyThrough(
            PharmacyDispensation::class,
            PrescriptionItem::class,
            'drug_id',
            'prescription_item_id'
        );
    }

    // Scopes
    public function scopeByDosageForm($query, $dosageForm)
    {
        return $query->where('dosage_form', $dosageForm);
    }

    public function scopeByStrength($query, $strength)
    {
        return $query->where('strength', $strength);
    }

    // Accessors
    public function getFormattedUnitPriceAttribute()
    {
        return number_format($this->unit_price, 2);
    }

    // Search
    public static function search($term)
    {
        return static::where('name', 'LIKE', "%{$term}%")
                    ->orWhere('description', 'LIKE', "%{$term}%")
                    ->orWhere('dosage_form', 'LIKE', "%{$term}%")
                    ->orWhere('strength', 'LIKE', "%{$term}%")
                    ->orWhere('unit', 'LIKE', "%{$term}%");
    }

    // Export/Import
    public static function getExportableFields()
    {
        return [
            'id',
            'name',
            'description',
            'dosage_form',
            'strength',
            'unit',
            'unit_price',
            'created_at',
            'updated_at'
        ];
    }
}
