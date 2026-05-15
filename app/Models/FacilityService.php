<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilityService extends Model
{
    use HasFactory;

    protected $table = 'facility_services';

    protected $fillable = [
        'facility_id',
        'service_item_id',
        'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the facility that owns this service.
     */
    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    /**
     * Get the service item.
     */
    public function serviceItem()
    {
        return $this->belongsTo(ServiceItem::class);
    }

    /**
     * Get availability badge HTML.
     */
    public function getAvailabilityBadge(): string
    {
        return $this->is_available
            ? '<span class="badge bg-success">Available</span>'
            : '<span class="badge bg-secondary">Unavailable</span>';
    }
}
