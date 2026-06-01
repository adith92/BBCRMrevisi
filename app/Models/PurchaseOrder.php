<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = ['po_number', 'vendor', 'item_description', 'amount', 'status', 'notes'];

    protected $casts = ['amount' => 'decimal:2'];
}
