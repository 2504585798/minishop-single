<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        'App\Events\Order\OrderCancelEvent'=>[
            'App\Listeners\Order\OrderCancelConfirmation'
        ],
        'App\Events\Pay\PayCreateEvent'=>[
            'App\Listeners\Pay\PayCreateConfirmation'
        ],
        'App\Events\Pay\PaySuccessEvent'=>[
            'App\Listeners\Pay\PaySuccessConfirmation'
        ],


    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
