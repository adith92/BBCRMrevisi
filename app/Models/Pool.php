<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pool extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'capacity',
        'notes',
        'region',
    ];

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}
