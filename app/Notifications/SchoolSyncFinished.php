<?php

namespace App\Notifications;

use App\Models\School;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SchoolSyncFinished extends Notification
{
    use Queueable;

    /**
     * @var School
     */
    public School $school;

    /**
     * Create a new notification instance.
     *
     * @param School $school
     */
    public function __construct(School $school)
    {
        $this->school = $school;
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
            ->subject('[' . config('app.name') . '] ' . __('School Sync Complete'))
            ->line(__('This is letting you know that the data for :school has finished syncing from PowerSchool.', [
                'school' => $this->school->name,
            ]));
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
