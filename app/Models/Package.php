<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
   use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'monthly_quota',
        'per_unit_price',
        'base_price',
        'excess_unit_multiplier',
        'weekend_unit_multiplier',
        'timings',
    ];

    protected $casts = [
        'monthly_quota' => 'integer',
        'per_unit_price' => 'decimal:2',
        'base_price' => 'decimal:2',
        'excess_unit_multiplier' => 'integer',
        'weekend_unit_multiplier' => 'integer',
        'timings' => 'array',
    ];

    public function clients()
    {
        return $this->hasMany(Client::class);
    }
}
