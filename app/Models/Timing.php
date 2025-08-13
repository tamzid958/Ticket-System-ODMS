<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timing extends Model
{
 use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'multiplier',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'multiplier' => 'integer',
    ];

    public function packages()
    {
        return $this->belongsToMany(Package::class, 'package_timings');
    }
}
