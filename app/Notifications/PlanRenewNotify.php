<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlanRenewNotify extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $plan_name;
    public $description;
    public $user_name;
    public function __construct($data)
    {
        $this->plan_name = $data['plan_name'];
        $this->description = $data['description'];
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
        //             ->from('maruti20021208@gmail.com', $this->user_name)
        //             ->subject('Composer request completed')
        //             ->greeting('Hello! ' . $this->composer_name)
        //             ->line('Thank you for using our application!');

        return (new MailMessage)       
            ->from('maruti20021208@gmail.com','Faristol')
            ->subject('Renew Plan Reminder')
            ->greeting('Hello! ' . $this->user_name)
            ->line('Your subscription plan is expired soon please renew your subscription plan.')
            ->line('Subscription Plan :'.$this->plan_name)
            ->line('Plan Description :'.$this->description);
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
