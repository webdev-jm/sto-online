<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UploadNotification extends Notification
{
    use Queueable;

    public $user_notification;
    public $notification;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user_notification)
    {
        $this->afterCommit();
        $this->user_notification = $user_notification;
        $this->notification = $user_notification->notification;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
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
            ->from($this->notification->from_email, $this->notification->from_name)
            ->subject($this->notification->subject)
            ->greeting('Hi! '.$notifiable->name)
            // ->line('This is a friendly reminder to upload your data to the BEV System. Ensuring that your data is up-to-date is crucial for the smooth operation and accuracy of our system. Please take a moment to log in and complete the necessary uploads at your earliest convenience. Your prompt attention to this matter is greatly appreciated.')
            ->line($this->notification->message)
            ->action($this->notification->link_name, url($this->notification->link_url))
            ->line('Thank you for using our application!');
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
            'status_code' => 'primary',
            'message' => $this->notification->message,
            'color' => 'primary',
            'url' => url($this->notification->link_url)
        ];
    }
}
