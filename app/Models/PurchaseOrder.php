<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;
    protected $table = 'purchase_orders';
    protected $fillable = ['po_number', 'vendor', 'item_description', 'amount', 'status', 'notes'];
    protected $casts = ['amount' => 'decimal:2'];
}
