<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = ['plate_number', 'brand', 'model', 'capacity', 'year', 'tier', 'status', 'pool_id', 'notes'];

    public function pool() { return $this->belongsTo(Pool::class); }
    public function bookings() { return $this->hasMany(Booking::class); }
    public function maintenanceLogs() { return $this->hasMany(MaintenanceLog::class); }
}
