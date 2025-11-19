<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Child extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes.
     * NOTE: Children are deleted and recreated on updates (with fresh BOSCHMA numbers).
     * boschma_no is set during creation only.
     */
    protected $fillable = [
        'beneficiary_id',
        'facility_id',
        'boschma_no',  // Will be primary beneficiary's number + B, C, D, or E
        'nin',
        'name',
        'dob',
        'gender',
        'birth_certificate_no',
        'birth_certificate_file',
        'photo',
        'remarks'
    ];

    /**
     * Get the beneficiary that owns the child.
     */
    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class);
    }

    /**
     * Get the facility associated with the child.
     */
    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
}
