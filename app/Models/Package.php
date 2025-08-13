<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
   use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'quota',
        'per_unit_price',
        'excess_unit_multiplier',
        'weekend_unit_multiplier',
    ];

    protected $casts = [
        'quota' => 'integer',
        'per_unit_price' => 'decimal:5,2',
        'excess_unit_multiplier' => 'integer',
        'weekend_unit_multiplier' => 'integer',
    ];

    public function timings()
    {
        return $this->belongsToMany(Timing::class, 'package_timings');
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }
}
