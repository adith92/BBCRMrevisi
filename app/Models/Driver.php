<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'license_number',
        'status',
        'notes',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function contracts()
    {
        return $this->hasMany(VehicleContract::class);
    }
}
