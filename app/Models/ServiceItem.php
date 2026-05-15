<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ServiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_type_id',
        'name',
        'description',
        'type',
        'price',
    ];

    protected $casts = [
        'id' => 'string',
        'service_type_id' => 'string',
        'price' => 'decimal:2',
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

        // Generate UUID for new service items
        static::creating(function ($serviceItem) {
            if (empty($serviceItem->id) || $serviceItem->id === '0') {
                $serviceItem->id = (string) Str::uuid();
                \Log::info('Generated UUID for service item: ' . $serviceItem->id);
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
     * Get the service type for this item.
     */
    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }

    /**
     * Get the service category for this item (through service type).
     */
    public function serviceCategory()
    {
        return $this->hasOneThrough(ServiceCategory::class, ServiceType::class, 'id', 'id', 'service_type_id', 'service_category_id');
    }

    public function serviceOrderItems()
    {
        return $this->hasMany(ServiceOrderItem::class, 'service_item_id');
    }

    /**
     * Get the formatted price.
     */
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2);
    }

    /**
     * Get the price with currency symbol.
     */
    public function getPriceWithCurrencyAttribute()
    {
        return '₦' . number_format($this->price, 2);
    }

    /**
     * Get the type badge HTML.
     */
    public function getTypeBadgeAttribute()
    {
        $badges = [
            'Primary' => '<span class="badge bg-primary">Primary</span>',
            'Secondary' => '<span class="badge bg-success">Secondary</span>',
            'Other' => '<span class="badge bg-light text-dark">Other</span>',
        ];

        return $badges[$this->type] ?? '<span class="badge bg-light text-dark">' . $this->type . '</span>';
    }

    /**
     * Get the full service path.
     */
    public function getFullServicePathAttribute()
    {
        if ($this->serviceType && $this->serviceType->serviceCategory) {
            return $this->serviceType->serviceCategory->name . ' > ' . $this->serviceType->name . ' > ' . $this->name;
        }
        
        return $this->name;
    }

    /**
     * Get available service types.
     */
    public static function getTypes()
    {
        return [
            'Primary' => 'Primary',
            'Secondary' => 'Secondary',
            'Other' => 'Other',
        ];
    }
}
