<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'price',
    ];

    protected $casts = [
        'id' => 'string',
        'price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Generate UUID for new services
        static::creating(function ($service) {
            if (empty($service->id) || $service->id === '0') {
                $uuid = (string) Str::uuid();
                $service->id = $uuid;
                \Log::info('Generated UUID for service: ' . $uuid);
            }
        });
    }

    /**
     * Get the service types as array.
     */
    public static function getTypes()
    {
        return [
            'Primary' => 'Primary',
            'Secondary' => 'Secondary',
        ];
    }

    /**
     * Get the type badge HTML.
     */
    public function getTypeBadgeAttribute()
    {
        $badges = [
            'Primary' => '<span class="badge bg-primary">Primary</span>',
            'Secondary' => '<span class="badge bg-secondary">Secondary</span>',
        ];

        return $badges[$this->type] ?? '<span class="badge bg-light text-dark">' . $this->type . '</span>';
    }

    /**
     * Get the facilities that provide this service
     */
    public function facilities()
    {
        return $this->belongsToMany(Facility::class, 'facility_has_services', 'service_id', 'facility_id')
                    ->withTimestamps();
    }

    /**
     * Get the formatted price attribute.
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
}
