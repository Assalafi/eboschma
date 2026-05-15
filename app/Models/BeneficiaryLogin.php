<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class BeneficiaryLogin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    protected $table = 'beneficiary_logins';

    protected $fillable = [
        'name',
        'email',
        'password',
        'civil_servant_id',
        'program_id',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the civil servant associated with this login.
     */
    public function civilServant()
    {
        return $this->belongsTo(CivilServant::class);
    }

    /**
     * Get the program associated with this login.
     */
    public function program()
    {
        return $this->belongsTo(Program::class);
    }
}
