<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Stamp extends Model
{
    use HasFactory;

    const STATUS_UNAPPROVED = 0;
    const STATUS_PENDING = 1;
    const STATUS_APPROVED =2;

    protected $fillable = [
        'staff_id',
        'stamp_date',
        'start_work',
        'end_work',
        'total_work',
        'status',
        'remarks',
    ];

    protected $casts = [
        'status' => 'integer',
        'stamp_date' => 'date',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function rests()
    {
        return $this->hasMany(Rest::class);
    }

    public function correctionRequests()
    {
        return $this->hasMany(AttendanceCorrectRequest::class);
    }

    public function restMinutes(): int
    {
        return $this->rests()
            ->whereNotNull('end_rest')
            ->get()
            ->sum(function($r) {
                return Carbon::parse($r->start_rest)->diffInMinutes(Carbon::parse($r->end_rest));
            });
    }

}
