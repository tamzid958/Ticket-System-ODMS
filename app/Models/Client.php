<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Overtrue\LaravelVersionable\Versionable;
use Overtrue\LaravelVersionable\VersionStrategy;

class Client extends Model
{
    use HasFactory, SoftDeletes, Versionable;

    protected $fillable = [
        'name',
        'package_id',
        'discount',
    ];

    protected $casts = [
        'package_id' => 'integer',
        'discount' => 'decimal:2'
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

    protected $versionStrategy = VersionStrategy::SNAPSHOT;

    protected $versionable = [
        'name',
        'package_id',
        'discount',
    ];

    public function versionedClient ()
    {
        $versionDate = Carbon::now()->startOfMonth();

        return $this->versionAt($versionDate);
    }
}
