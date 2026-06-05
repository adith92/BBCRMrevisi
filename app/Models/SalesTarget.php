<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class SalesTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'period_year',
        'period_month',
        'target_meetings',
        'target_calls',
        'target_visits',
        'target_opportunities',
        'target_won',
        'target_revenue',
        'actual_meetings',
        'actual_calls',
        'actual_visits',
        'actual_opportunities',
        'actual_won',
        'actual_revenue',
    ];

    protected $casts = [
        'target_revenue' => 'decimal:2',
        'actual_revenue' => 'decimal:2',
        'target_meetings' => 'integer',
        'target_calls' => 'integer',
        'target_visits' => 'integer',
        'target_opportunities' => 'integer',
        'target_won' => 'integer',
        'actual_meetings' => 'integer',
        'actual_calls' => 'integer',
        'actual_visits' => 'integer',
        'actual_opportunities' => 'integer',
        'actual_won' => 'integer',
        'period_year' => 'integer',
        'period_month' => 'integer',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    protected function meetingAchievement(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ((int) $this->target_meetings === 0) {
                    return 0;
                }
                return round(($this->actual_meetings / $this->target_meetings) * 100, 2);
            }
        );
    }

    protected function revenueAchievement(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ((float) $this->target_revenue == 0) {
                    return 0;
                }
                return round(((float) $this->actual_revenue / (float) $this->target_revenue) * 100, 2);
            }
        );
    }

    // Static helpers
    public static function getOrCreate(int $userId, int $year, int $month): self
    {
        return static::firstOrCreate(
            [
                'user_id' => $userId,
                'period_year' => $year,
                'period_month' => $month,
            ],
            [
                'target_meetings' => 0,
                'target_calls' => 0,
                'target_visits' => 0,
                'target_opportunities' => 0,
                'target_won' => 0,
                'target_revenue' => 0,
                'actual_meetings' => 0,
                'actual_calls' => 0,
                'actual_visits' => 0,
                'actual_opportunities' => 0,
                'actual_won' => 0,
                'actual_revenue' => 0,
            ]
        );
    }
}
