<?php

namespace App\Providers;

use App\Events\InvoiceImportFinished;
use App\Events\PaymentImportFinished;
use App\Listeners\LogFailedLogin;
use App\Listeners\LogLogin;
use App\Listeners\SendPaymentImportFinishedNotification;
use App\Listeners\SetUserSchool;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

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
        Login::class => [
//            SetUserSchool::class,
            LogLogin::class,
        ],
        Failed::class => [
            LogFailedLogin::class,
        ],
        InvoiceImportFinished::class => [],
        PaymentImportFinished::class => [
            SendPaymentImportFinishedNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
