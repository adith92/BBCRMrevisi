<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_code',
        'client_id',
        'product_id',
        'title',
        'denomination',
        'purchase_price',
        'valid_from',
        'valid_until',
        'status',
        'used_at',
        'used_by_booking_id',
        'issued_by',
        'notes',
    ];

    protected $casts = [
        'denomination' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'used_at' => 'datetime',
    ];

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function usedByBooking()
    {
        return $this->belongsTo(Booking::class, 'used_by_booking_id');
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeExpired($query)
    {
        return $query->where('valid_until', '<', now()->toDateString())
            ->where('status', 'available');
    }
}
