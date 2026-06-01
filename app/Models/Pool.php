<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pool extends Model
{
    protected $fillable = ['name', 'location', 'capacity', 'notes'];

    public function vehicles() { return $this->hasMany(Vehicle::class); }
}
