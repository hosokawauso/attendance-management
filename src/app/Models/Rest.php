<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Rest extends Model
{
    use HasFactory;

    protected $fillable = [
        'stamp_id',
        'stamp_date',
        'start_rest',
        'end_rest',
        'total_rest'
    ];

    protected $casts =[
        'stamp_date' => 'date',
        'start_rest' => 'datetime',
        'end_rest' => 'datetime',
    ];

    public function staff()
    {
        return $this->belongsTo(Stamp::class);
    }

}
