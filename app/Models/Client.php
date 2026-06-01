<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = ['company_name', 'pic_name', 'phone', 'email', 'address', 'industry', 'status', 'assigned_sales_id', 'notes'];

    public function assignedSales() { return $this->belongsTo(User::class, 'assigned_sales_id'); }
    public function bookings() { return $this->hasMany(Booking::class); }
    public function invoices() { return $this->hasMany(Invoice::class); }
    public function meetingLogs() { return $this->hasMany(MeetingLog::class); }
}
