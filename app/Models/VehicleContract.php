<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleContract extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'driver_id',
        'client_id',
        'start_date',
        'end_date',
        'contract_type',
        'rate',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'rate' => 'decimal:2',
    ];

    // Relationships
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
