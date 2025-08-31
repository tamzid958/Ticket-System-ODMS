<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientMonthlySummary extends Model
{
     use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'package_id',
        'year',
        'month',

        'monthly_quota',
        'excess_per_unit_price',
        'package_price',
        'package_discount',

        'unit_used',
        'excess_unit_used',
        'unit_discount',

        'total_cost',
        'excess_unit_cost',
    ];

    protected $casts = [
        'client_id' => 'integer',
        'package_id' => 'integer',
        'year' => 'integer',
        'month' => 'integer',
        'monthly_quota' => 'integer',
        'excess_per_unit_price' => 'decimal:2',
        'package_price' => 'decimal:2',
        'package_discount' => 'decimal:2',
        'unit_used' => 'integer',
        'excess_unit_used' => 'integer',
        'unit_discount' => 'integer',
        'total_cost' => 'decimal:2',
        'excess_unit_cost' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }
}
