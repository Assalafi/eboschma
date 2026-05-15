<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Authorization extends Model
{
    use HasFactory;

    protected $fillable = [
        'authorization_code',
        'patient_id',
        'encounter_id',
        'service_referral_id',
        'approved_by',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the patient that owns the authorization.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the encounter that owns the authorization.
     */
    public function encounter()
    {
        return $this->belongsTo(Encounter::class);
    }

    /**
     * Get the service referral that owns the authorization.
     */
    public function serviceReferral()
    {
        return $this->belongsTo(ServiceReferral::class);
    }

    /**
     * Get the user who approved the authorization.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Generate a unique authorization code
     */
    public static function generateCode()
    {
        do {
            $code = 'AUTH' . date('Ymd') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('authorization_code', $code)->exists());

        return $code;
    }

    /**
     * Check if authorization is expired
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if authorization is valid
     */
    public function isValid()
    {
        return !$this->isExpired();
    }
}
