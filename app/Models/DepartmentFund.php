<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DepartmentFund extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['department_id', 'amount', 'session', 'sector'];

    // Define relationship with Department
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // Override the incrementing property to use UUIDs instead of incremental IDs
    public $incrementing = false;
    
    // Override the keyType property to ensure UUID compatibility
    protected $keyType = 'string';
    
    // Unique constraint is implemented in migration
}
