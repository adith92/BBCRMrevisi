<?php

namespace App\Models;

use App\Services\KpiService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_id',
        'client_id',
        'opportunity_id',
        'type',
        'subject',
        'notes',
        'activity_date',
        'duration_minutes',
        'outcome',
        'next_action',
        'next_action_date',
    ];

    protected $casts = [
        'activity_date' => 'datetime',
        'next_action_date' => 'date',
        'duration_minutes' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function (ActivityLog $activityLog) {
            KpiService::incrementActivityCount($activityLog);
        });
    }

    // Relationships
    public function sales()
    {
        return $this->belongsTo(User::class, 'sales_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function opportunity()
    {
        return $this->belongsTo(Opportunity::class);
    }
}
