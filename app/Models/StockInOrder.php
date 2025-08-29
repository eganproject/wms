<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockInOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'date',
        'warehouse_id',
        'status',
        'description',
        'requested_by',
        'approved_by',
        'completed_at',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function items()
    {
        return $this->hasMany(StockInOrderItem::class);
    }
}