<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'format',
        'has_dependant',
        'status',
    ];

    protected $casts = [
        'has_dependant' => 'boolean',
        'status' => 'boolean',
    ];

    /**
     * Scope to filter active programs
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope to filter inactive programs
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 0);
    }

    /**
     * Scope to filter programs with dependants
     */
    public function scopeWithDependants($query)
    {
        return $query->where('has_dependant', 1);
    }

    /**
     * Scope to filter programs without dependants
     */
    public function scopeWithoutDependants($query)
    {
        return $query->where('has_dependant', 0);
    }
}
