<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceOrderItem extends Model
{
    protected $fillable = [
        'service_order_id',
        'service_item_id',
        'authorization_code',
        'authorization_expires_at',
        'status'
    ];

    protected $casts = [
        'authorization_expires_at' => 'datetime',
    ];

    public function serviceOrder()
    {
        return $this->belongsTo(ServiceOrder::class, 'service_order_id');
    }

    public function serviceItem()
    {
        return $this->belongsTo(ServiceItem::class, 'service_item_id');
    }
}
