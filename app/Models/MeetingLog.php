<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingLog extends Model
{
    protected $fillable = ['client_id', 'sales_id', 'meeting_date', 'notes', 'outcome', 'follow_up_date', 'status'];

    protected $casts = [
        'meeting_date' => 'datetime',
        'follow_up_date' => 'date',
    ];

    public function client() { return $this->belongsTo(Client::class); }
    public function sales() { return $this->belongsTo(User::class, 'sales_id'); }
}
