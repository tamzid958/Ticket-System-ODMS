<?php

namespace App\Models;

use App\Support\VersionHelper;
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
        'will_generate_report
'
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
        'will_generate_report'
    ];

    public function versionedInstance(Carbon $date = null)
    {
        $versionDate = VersionHelper::getCostingDate($date);
        $client = $this->versionAt($versionDate)?->revertWithoutSaving();

        $client ??= $this->versions()
            ->where('created_at', '>=', $versionDate)
            ->orderBy('created_at')
            ->first()
            ?->revertWithoutSaving();

        $client ??= $this;

        return $client;
    }
}
