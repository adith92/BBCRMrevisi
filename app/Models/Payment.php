<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['payment_number', 'invoice_id', 'amount', 'method', 'payment_date', 'notes'];
    protected $casts = ['payment_date' => 'date', 'amount' => 'decimal:2'];

    public function invoice() { return $this->belongsTo(Invoice::class); }
}
