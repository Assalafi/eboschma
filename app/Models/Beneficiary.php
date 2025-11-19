<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
