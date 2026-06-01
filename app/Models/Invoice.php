<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = ['invoice_number', 'booking_id', 'client_id', 'amount', 'status', 'due_date', 'paid_at', 'notes'];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function booking() { return $this->belongsTo(Booking::class); }
    public function client() { return $this->belongsTo(Client::class); }
    public function payments() { return $this->hasMany(Payment::class); }
}
