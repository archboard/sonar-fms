<?php

namespace App\Listeners;

use App\Events\PaymentImportFinished;
use App\Notifications\PaymentImportFinished as Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPaymentImportFinishedNotification implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(PaymentImportFinished $event)
    {
        $event->import->user->notify(new Notification($event->import));
    }
}
