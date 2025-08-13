<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'package_id',
    ];

    protected $casts = [
        'package_id' => 'integer',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

     public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
