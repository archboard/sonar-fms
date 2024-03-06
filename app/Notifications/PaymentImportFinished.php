<?php

namespace App\Notifications;

use App\Models\PaymentImport;
use App\Traits\AddsNotificationSubject;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentImportFinished extends Notification
{
    use AddsNotificationSubject;
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(protected PaymentImport $import)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject($this->makeSubject(__('Payment import finished')))
            ->line(__('Your payment import has finished processing. All the invoice balances have been updated to reflect the payments that have been imported.'))
            ->action(__('View import'), route('payments.imports.show', $this->import));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
