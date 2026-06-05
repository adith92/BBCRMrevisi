<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'booking_id',
        'client_id',
        'amount',
        'status',
        'due_date',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_number',
        'invoice_id',
        'amount',
        'method',
        'payment_date',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'vendor',
        'item_description',
        'amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    protected $table = 'purchase_orders';
}

class Pool extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'capacity',
        'notes',
    ];

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}

class MaintenanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'type',
        'description',
        'cost',
        'vendor',
        'scheduled_date',
        'completed_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_date' => 'date',
        'cost' => 'decimal:2',
    ];

    protected $table = 'maintenance_logs';

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}

class MeetingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'sales_id',
        'meeting_date',
        'notes',
        'outcome',
        'follow_up_date',
        'status',
    ];

    protected $casts = [
        'meeting_date' => 'date',
        'follow_up_date' => 'date',
    ];

    protected $table = 'meeting_logs';

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function sales()
    {
        return $this->belongsTo(User::class, 'sales_id');
    }
}
