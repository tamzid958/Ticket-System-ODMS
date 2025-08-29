<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Mail\Events\MessageSent;
use App\Listeners\UpdateMailStatusListener;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        MessageSent::class => [
            UpdateMailStatusListener::class,
        ],
    ];

    public function boot()
    {
        //
    }
}