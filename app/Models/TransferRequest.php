<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferRequest extends Model
{
    protected $table = 'transfer_requests';
    protected $fillable = [
        'code',
        'from_warehouse_id',
        'to_warehouse_id',
        'date',
        'description',
        'status',
        'requested_by',
        'approved_by',
        'completed_at',
    ];

    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }
    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function items()
    {
        return $this->hasMany(TransferRequestItem::class);
    }
}
