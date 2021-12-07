<?php

namespace App\Listeners;

use App\Events\PaymentImportFinished;
use App\Notifications\PaymentImportFinished as Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPaymentImportFinishedNotification implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param PaymentImportFinished $event
     * @return void
     */
    public function handle(PaymentImportFinished $event)
    {
        $event->import->user->notify(new Notification($event->import));
    }
}
