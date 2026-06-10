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
        'opportunity_id' => 'integer',
        'requested_by' => 'integer',
        'current_approver_id' => 'integer',
        'discount_percent' => 'decimal:2',
        'original_price' => 'decimal:2',
        'final_price' => 'decimal:2',
        'level' => 'integer',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

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
}
