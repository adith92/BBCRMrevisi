<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingLog extends Model
{
    use HasFactory;
    protected $table = 'meeting_logs';
    protected $fillable = ['client_id', 'sales_id', 'meeting_date', 'notes', 'outcome', 'follow_up_date', 'status'];
    protected $casts = ['meeting_date' => 'date', 'follow_up_date' => 'date'];
    public function client() { return $this->belongsTo(Client::class); }
    public function sales() { return $this->belongsTo(User::class, 'sales_id'); }
}
