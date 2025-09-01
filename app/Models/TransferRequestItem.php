<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferRequestItem extends Model
{
    protected $table = 'transfer_request_items';
    protected $fillable = [
        'transfer_request_id',
        'item_id',
        'quantity',
        'koli',
        'description',
    ];
}
