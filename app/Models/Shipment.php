<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'transfer_request_id',
        'shipping_date',
        'vehicle_type',
        'license_plate',
        'driver_name',
        'driver_contact',
        'description',
        'status',
        'shipped_by',
    ];
}