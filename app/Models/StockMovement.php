<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $table = 'stock_movements';

    protected $fillable = [
        'item_id',
        'warehouse_id',
        'date',
        'quantity',
        'koli',
        'type',
        'description',
        'user_id',
        'reference_id',
        'reference_type',
    ];
    
}
