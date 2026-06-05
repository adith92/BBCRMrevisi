<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'sub_number',
        'opportunity_id',
        'client_id',
        'vehicle_id',
        'driver_id',
        'product_id',
        'start_date',
        'end_date',
        'monthly_rate',
        'billing_cycle',
        'status',
        'last_billed_at',
        'next_billing_date',
        'auto_renew',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'last_billed_at' => 'date',
        'next_billing_date' => 'date',
        'monthly_rate' => 'decimal:2',
        'auto_renew' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($subscription) {
            if (empty($subscription->sub_number)) {
                $yearMonth = now()->format('Ym');
                $prefix = 'SUB-' . $yearMonth . '-';
                $lastSub = static::where('sub_number', 'like', $prefix . '%')
                    ->orderByDesc('sub_number')
                    ->first();

                if ($lastSub) {
                    $lastSeq = (int) substr($lastSub->sub_number, -4);
                    $seq = $lastSeq + 1;
                } else {
                    $seq = 1;
                }

                $subscription->sub_number = $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function opportunity()
    {
        return $this->belongsTo(Opportunity::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Accessors
    protected function formattedRate(): Attribute
    {
        return Attribute::make(
            get: fn () => 'Rp ' . number_format((float) $this->monthly_rate, 0, ',', '.')
        );
    }
}
