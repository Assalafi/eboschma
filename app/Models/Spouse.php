<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Spouse extends Model
{
    use HasFactory;
    
    /**
     * Mass assignable attributes.
     * NOTE: boschma_no CAN be set during creation,
     * but should NEVER be updated after initial assignment (enforced in controllers).
     */
    protected $fillable = [
        'beneficiary_id',
        'facility_id',
        'boschma_no', // Will be primary beneficiary's number + 'A'
        'nin',
        'name',
        'dob',
        'gender',
        'phone',
        'email',
        'photo',
        'remarks',
        'created_by',
        'updated_by',
        'submitted_by',
    ];
    
    /**
     * Get the beneficiary that owns the spouse.
     */
    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class);
    }

    /**
     * Get the facility associated with the spouse.
     */
    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
}
