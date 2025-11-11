<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Stamp extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'stamp_date',
        'start_work',
        'end_work',
        'total_work',
        'approved',
        'remarks',
    ];

    protected $casts = [
        'approved' => 'boolean',
        'stamp_date' => 'date',
        'start_work' => 'datetime',
        'end_work' => 'datetime',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function rests()
    {
        return $this->hasMany(Rest::class);
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
