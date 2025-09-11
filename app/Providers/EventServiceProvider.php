<?php

namespace App\Providers;

use App\Listeners\LogSentMessage;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Mail\Events\MessageSent;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        MessageSent::class => [
            LogSentMessage::class,
        ],
    ];

    public function boot()
    {
        //
    }
}
