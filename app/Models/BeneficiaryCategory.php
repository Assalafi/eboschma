<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeneficiaryCategory extends Model
{
    use HasFactory;

    protected $table = 'beneficiary_categories';

    protected $fillable = ['name'];
}
