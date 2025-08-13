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
        'year',
        'month',

        'monthly_quota',

        'unit_used',
        'unit_remaining',

        'weekend_unit_used',

        'excess_unit_used',

        'total_cost',
    ];

    protected $casts = [
        'client_id' => 'integer',
        'year' => 'integer',
        'month' => 'integer',
        'monthly_quota' => 'integer',
        'unit_used' => 'integer',
        'unit_remaining' => 'integer',
        'unit_cost' => 'decimal:2',
        'weekend_unit_used' => 'integer',
        'weekend_unit_remaining' => 'integer',
        'excess_unit_used' => 'integer',
        'excess_unit_remaining' => 'integer',
        'total_cost' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }
}
