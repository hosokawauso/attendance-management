<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrectRequest extends Model
{
    use HasFactory;

    const STATUS_PENDING    = 1; //承認待ち
    const STATUS_APPROVED   = 2; //承認済み

    protected $fillable = [
        'stamp_id',
        'staff_id',
        'status',
        'requested_start_work',
        'requested_end_work',
        'requested_remarks',
        'admin_comment',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'requested_start_work' => 'datetime:H:i',
        'requested_end_work' => 'datetime:H:i',
        'approved_at' => 'datetime',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function stamp()
    {
        return $this->belongsTo(Stamp::class);
    }

    public function rests()
    {
        return $this->hasMany(AttendanceCorrectRequestRest::class)
            ->orderBy('sort_order');
    }

}
