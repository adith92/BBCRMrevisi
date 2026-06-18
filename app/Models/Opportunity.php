<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Opportunity extends Model
{
    use HasFactory;

    protected $fillable = [
        'opp_number',
        'client_id',
        'sales_id',
        'product_id',
        'title',
        'stage',
        'estimated_value',
        'final_value',
        'pax',
        'discount_percent',
        'discount_approved',
        'approved_by',
        'expected_close_date',
        'actual_close_date',
        'lost_reason',
        'notes',
        'booking_id',
        'subscription_id',
        'products',
        'history_timeline',
        'stage_changed_at',
        'contract_duration_months',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'final_value' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_approved' => 'boolean',
        'expected_close_date' => 'date',
        'actual_close_date' => 'date',
        'pax' => 'integer',
        'products' => 'array',
        'history_timeline' => 'array',
        'stage_changed_at' => 'datetime',
        'contract_duration_months' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($opportunity) {
            if (empty($opportunity->opp_number)) {
                $yearMonth = now()->format('Ym');
                $prefix = 'OPP-' . $yearMonth . '-';
                $lastOpp = static::where('opp_number', 'like', $prefix . '____')
                    ->orderByDesc('opp_number')
                    ->first();

                if ($lastOpp) {
                    $lastSeq = (int) substr($lastOpp->opp_number, -4);
                    $seq = $lastSeq + 1;
                } else {
                    $seq = 1;
                }

                $opportunity->opp_number = $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
            }
            if (empty($opportunity->stage_changed_at)) {
                $opportunity->stage_changed_at = now();
            }
        });
    }

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function sales()
    {
        return $this->belongsTo(User::class, 'sales_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function approvalRequests()
    {
        return $this->hasMany(ApprovalRequest::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function assignedVehicles()
    {
        return $this->hasMany(Vehicle::class, 'assigned_opportunity_id');
    }

    public function assignedDrivers()
    {
        return $this->hasMany(Driver::class, 'assigned_opportunity_id');
    }

    // Scopes
    public function scopeByStage($query, string $stage)
    {
        return $query->where('stage', $stage);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('stage', ['won', 'lost']);
    }

    // Accessors
    protected function stageColor(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->stage) {
                    'call_meeting' => 'purple',
                    'prospecting'  => 'blue',
                    'proposal'     => 'yellow',
                    'negotiation'  => 'orange',
                    'won'          => 'green',
                    'lost'         => 'red',
                    default        => 'gray',
                };
            }
        );
    }

    protected function stageLabel(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->stage) {
                    'call_meeting' => 'Call Meeting',
                    'prospecting'  => 'Prospekting',
                    'proposal'     => 'Proposal',
                    'negotiation'  => 'Negosiasi',
                    'won'          => 'Menang',
                    'lost'         => 'Kalah',
                    default        => ucfirst($this->stage),
                };
            }
        );
    }

    public function requiredFleetQty(): int
    {
        $qty = 0;
        $hasProductRows = !empty($this->products) && is_array($this->products);

        if ($hasProductRows) {
            foreach ($this->products as $p) {
                $cat = strtolower($p['category'] ?? '');
                $name = strtolower($p['name'] ?? $p['product_name'] ?? '');
                if ($this->isFleetProductText($cat) || $this->isFleetProductText($name)) {
                    $qty += (int)($p['quantity'] ?? 0);
                }
            }
        }

        if ($qty === 0 && !$hasProductRows && (int) $this->pax > 0) {
            $qty = (int) $this->pax;
        }

        if ($qty === 0 && $this->product_id) {
            $this->loadMissing('product.category');
            if ($this->product) {
                $prodName = strtolower($this->product->name);
                $catName = $this->product->category ? strtolower($this->product->category->name) : '';
                if ($this->isFleetProductText($prodName) || $this->isFleetProductText($catName)) {
                    if (preg_match('/—\s*(\d+)\s*unit/i', $this->title, $matches)) {
                        $qty = (int)$matches[1];
                    } else {
                        $qty = 1;
                    }
                }
            }
        }

        return $qty;
    }

    public function requiredDriverQty(): int
    {
        $qty = 0;
        $hasProductRows = !empty($this->products) && is_array($this->products);

        if ($hasProductRows) {
            foreach ($this->products as $p) {
                $cat = strtolower($p['category'] ?? '');
                $name = strtolower($p['name'] ?? $p['product_name'] ?? '');
                if ($this->isDriverProductText($cat) || $this->isDriverProductText($name)) {
                    $qty += (int)($p['quantity'] ?? 0);
                }
            }
        }

        if ($qty === 0 && !$hasProductRows && (int) $this->pax > 0) {
            $qty = (int) $this->pax;
        }

        if ($qty === 0 && $this->product_id) {
            $this->loadMissing('product.category');
            if ($this->product) {
                $prodName = strtolower($this->product->name);
                $catName = $this->product->category ? strtolower($this->product->category->name) : '';
                if ($this->isDriverProductText($prodName) || $this->isDriverProductText($catName)) {
                    if (preg_match('/—\s*(\d+)\s*(unit|orang|driver|supir)/i', $this->title, $matches)) {
                        $qty = (int)$matches[1];
                    } else {
                        $qty = 1;
                    }
                }
            }
        }

        // Optional fallback: uncomment the line below if you want 1 driver per vehicle when no Driver product is present.
        // $qty = $this->requiredFleetQty();

        return $qty;
    }

    public function demoRequiredFleetQty(int $max = 10): int
    {
        return min($this->requiredFleetQty(), $max);
    }

    public function demoRequiredDriverQty(int $max = 10): int
    {
        return min($this->requiredDriverQty(), $max);
    }

    private function isFleetProductText(string $value): bool
    {
        $value = trim(strtolower($value));

        return $value === 'mobil long term'
            || $value === 'long term'
            || str_contains($value, 'mobil long')
            || str_contains($value, 'long term fleet');
    }

    private function isDriverProductText(string $value): bool
    {
        $value = trim(strtolower($value));

        return $value === 'supir'
            || $value === 'driver'
            || $value === 'service'
            || str_contains($value, 'supir')
            || str_contains($value, 'driver');
    }
}
