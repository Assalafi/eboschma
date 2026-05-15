<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ServiceType extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_category_id',
        'name',
    ];

    protected $casts = [
        'id' => 'string',
        'service_category_id' => 'string',
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

        // Generate UUID for new service types
        static::creating(function ($serviceType) {
            if (empty($serviceType->id) || $serviceType->id === '0') {
                $serviceType->id = (string) Str::uuid();
                \Log::info('Generated UUID for service type: ' . $serviceType->id);
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
     * Get the service category for this type.
     */
    public function serviceCategory()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    /**
     * Get the service items for this type.
     */
    public function serviceItems()
    {
        return $this->hasMany(ServiceItem::class, 'service_type_id');
    }

    /**
     * Check if the type has service items.
     */
    public function getHasServiceItemsAttribute()
    {
        return $this->serviceItems()->count() > 0;
    }

    /**
     * Get the count of service items.
     */
    public function getServiceItemsCountAttribute()
    {
        return $this->serviceItems()->count();
    }

    /**
     * Get the category name with type name.
     */
    public function getCategoryWithTypeAttribute()
    {
        return $this->serviceCategory->name . ' - ' . $this->name;
    }
}
