<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrectRequestRest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_correct_request_id',
        'requested_start_rest',
        'requested_end_rest',
        'sort_order',
    ];

    public function request()
    {
        return $this->belongsTo(AttendanceCorrectRequest::class, 'attendance_correct_request_id');
    }

    public function stamp()
{
    return $this->belongsTo(Stamp::class);
}
}
