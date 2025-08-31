<?php

namespace App\Models;

use App\Support\VersionHelper;
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

    public function versionedInstance ()
    {
        $versionDate = VersionHelper::getCostingDate();
        $client = $this->versionAt($versionDate);
        if(!$client){
            $client = $this->versions()
                ->where('created_at', '<=', $versionDate)
                ->orderBy('created_at', 'desc')
                ->first();
        }
        return $client;
    }
}
