<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Role can be: director|gm|manager|sales|operational|finance
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'manager_id',
        'sales_level',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relationships
    public function clients()
    {
        return $this->hasMany(Client::class, 'assigned_sales_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'sales_id');
    }

    public function meetingLogs()
    {
        return $this->hasMany(MeetingLog::class, 'sales_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function subordinates()
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    public function opportunities()
    {
        return $this->hasMany(Opportunity::class, 'sales_id');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class, 'sales_id');
    }

    public function salesTargets()
    {
        return $this->hasMany(SalesTarget::class);
    }

    public function approvalRequests()
    {
        return $this->hasMany(ApprovalRequest::class, 'requested_by');
    }

    // Role Checks
    public function isDirector(): bool
    {
        return $this->role === 'director';
    }

    public function isGM(): bool
    {
        return $this->role === 'gm';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isSales(): bool
    {
        return $this->role === 'sales';
    }

    public function isOperational(): bool
    {
        return $this->role === 'operational';
    }

    public function isFinance(): bool
    {
        return $this->role === 'finance';
    }
}
