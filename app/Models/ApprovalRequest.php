<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'opportunity_id',
        'requested_by',
        'current_approver_id',
        'type',
        'discount_percent',
        'original_price',
        'final_price',
        'level',
        'status',
        'notes',
        'rejection_reason',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'discount_percent' => 'decimal:2',
        'original_price' => 'decimal:2',
        'final_price' => 'decimal:2',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'level' => 'integer',
    ];

    // Relationships
    public function opportunity()
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function currentApprover()
    {
        return $this->belongsTo(User::class, 'current_approver_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForApprover($query, int $userId)
    {
        return $query->where('current_approver_id', $userId);
    }
}
