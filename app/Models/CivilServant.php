<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CivilServant extends Model
{
    use HasFactory;

    protected $fillable = [
        'dp_no',
        'nin',
        'bvn',
        'fullname',
        'dob',
        'state',
        'lga',
        'gender',
        'mda',
    ];

    protected $casts = [
        'dob' => 'date',
    ];
}
