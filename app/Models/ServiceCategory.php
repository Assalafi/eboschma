<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ServiceCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    protected $casts = [
        'id' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Generate UUID for new service categories
        static::creating(function ($serviceCategory) {
            if (empty($serviceCategory->id) || $serviceCategory->id === '0') {
                $serviceCategory->id = (string) Str::uuid();
                \Log::info('Generated UUID for service category: ' . $serviceCategory->id);
            }
        });
    }

    /**
     * Get the route key for the model.
     * Using id for routing
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Get the service types for this category.
     */
    public function serviceTypes()
    {
        return $this->hasMany(ServiceType::class, 'service_category_id');
    }

    /**
     * Get the service items for this category (through service types).
     */
    public function serviceItems()
    {
        return $this->hasManyThrough(ServiceItem::class, ServiceType::class, 'service_category_id', 'service_type_id');
    }

    /**
     * Check if the category has service types.
     */
    public function getHasServiceTypesAttribute()
    {
        return $this->serviceTypes()->count() > 0;
    }

    /**
     * Get the count of service types.
     */
    public function getServiceTypesCountAttribute()
    {
        return $this->serviceTypes()->count();
    }

    /**
     * Get the count of service items.
     */
    public function getServiceItemsCountAttribute()
    {
        return $this->serviceItems()->count();
    }
}
