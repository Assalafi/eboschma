<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_number',
        'enrollee_number',
        'enrollee_type',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Get the enrollee details based on type
     */
    public function getEnrolleeDetailsAttribute()
    {
        switch ($this->enrollee_type) {
            case 'beneficiary':
                return Beneficiary::where('boschma_no', $this->enrollee_number)->first();
            case 'spouse':
                return Spouse::where('boschma_no', $this->enrollee_number)->first();
            case 'child':
                return Child::where('boschma_no', $this->enrollee_number)->first();
            default:
                return null;
        }
    }

    /**
     * Get enrollee details as a relationship for filtering
     */
    public function enrolleeDetails()
    {
        switch ($this->enrollee_type) {
            case 'beneficiary':
                return $this->hasOne(Beneficiary::class, 'boschma_no', 'enrollee_number');
            case 'spouse':
                return $this->hasOne(Spouse::class, 'boschma_no', 'enrollee_number')->with('beneficiary');
            case 'child':
                return $this->hasOne(Child::class, 'boschma_no', 'enrollee_number')->with('beneficiary');
            default:
                return $this->hasOne(Beneficiary::class, 'boschma_no', 'enrollee_number')->whereRaw('1=0');
        }
    }

    /**
     * Get the beneficiary relationship
     */
    public function beneficiary()
    {
        return $this->hasOne(Beneficiary::class, 'boschma_no', 'enrollee_number');
    }

    /**
     * Get the spouse relationship
     */
    public function spouse()
    {
        return $this->hasOne(Spouse::class, 'boschma_no', 'enrollee_number');
    }

    /**
     * Get the child relationship
     */
    public function child()
    {
        return $this->hasOne(Child::class, 'boschma_no', 'enrollee_number');
    }

    /**
     * Get full enrollee information with all details
     */
    public function getFullInfoAttribute()
    {
        $details = $this->enrolleeDetails;
        
        if (!$details) {
            return null;
        }

        return [
            'id' => $this->id,
            'file_number' => $this->file_number,
            'enrollee_number' => $this->enrollee_number,
            'enrollee_type' => $this->enrollee_type,
            'fullname' => $details->fullname ?? '',
            'boschma_no' => $this->enrollee_number,
            'nin' => $details->nin ?? '',
            'gender' => $details->gender ?? '',
            'date_of_birth' => $details->date_of_birth ?? '',
            'phone_no' => $details->phone_no ?? $details->phone ?? '',
            'email' => $details->email ?? '',
        ];
    }

    /**
     * Scope to search patients
     */
    public function scopeSearch($query, $term)
    {
        return $query->where('enrollee_number', 'LIKE', "%{$term}%")
                    ->orWhere('file_number', 'LIKE', "%{$term}%");
    }
}
