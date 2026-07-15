<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;


class ComposerStatusNotify extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $composer_name;
    public $denied_reason;
    public $user_name;
    public function __construct($data)
    {
        $this->composer_name = $data['composer_name'];
        if(isset($data['denied_reason'])){
            $this->denied_reason = $data['denied_reason'];
        }
        $this->user_name = $data['user_name'];
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // return (new MailMessage)
        //             ->line('The introduction to the notification.')
        //             ->action('Notification Action', url('/'))
        //             ->line('Thank you for using our application!');
        if(isset($this->denied_reason)){
            return (new MailMessage)       
            ->from('maruti20021208@gmail.com', $this->user_name)
            ->subject('Composer request completed')
            ->greeting('Hello! ' . $this->composer_name)
            ->line($this->denied_reason);
        }else{
            return (new MailMessage)       
            ->from('maruti20021208@gmail.com', $this->user_name)
            ->subject('Composer request completed')
            ->greeting('Hello! ' . $this->composer_name)
            ->line('Your Composer request has been approved');
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
