<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceLog extends Model
{
    protected $fillable = ['vehicle_id', 'type', 'description', 'cost', 'vendor', 'scheduled_date', 'completed_date', 'status', 'notes'];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_date' => 'date',
        'cost' => 'decimal:2',
    ];

    public function vehicle() { return $this->belongsTo(Vehicle::class); }
}
