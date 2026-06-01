<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function clients() { return $this->hasMany(Client::class, 'assigned_sales_id'); }
    public function bookings() { return $this->hasMany(Booking::class, 'sales_id'); }
    public function createdBookings() { return $this->hasMany(Booking::class, 'created_by'); }
    public function meetingLogs() { return $this->hasMany(MeetingLog::class, 'sales_id'); }
}
