<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Break extends Model
{
    use HasFactory;

        protected $fillable = [
        'stamp_id',
        'stamp_date',
        'start_break',
        'end_break',
        'total_break'
    ];

    protected $casts =[
        'stamp_date' => 'date',
        'start_break' => 'datetime',
        'end_break' => 'datetime',
    ];

    public function staff()
    {
        return $this->belongsTo(Stamp::class);
    }


}
