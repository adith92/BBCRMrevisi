<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = ['booking_number', 'client_id', 'sales_id', 'created_by', 'vehicle_id', 'driver_id', 'pickup_datetime', 'dropoff_datetime', 'destination', 'vehicle_type', 'price', 'status', 'notes'];

    protected $casts = [
        'pickup_datetime' => 'datetime',
        'dropoff_datetime' => 'datetime',
        'price' => 'decimal:2',
    ];

    public function client() { return $this->belongsTo(Client::class); }
    public function sales() { return $this->belongsTo(User::class, 'sales_id'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function vehicle() { return $this->belongsTo(Vehicle::class); }
    public function driver() { return $this->belongsTo(Driver::class); }
    public function invoice() { return $this->hasOne(Invoice::class); }
}
