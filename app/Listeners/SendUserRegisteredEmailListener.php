<?php

namespace App\Listeners;

use App\Mail\UserRegisteredEmail;
use Illuminate\Support\Facades\Mail;

class SendUserRegisteredEmailListener
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(object $event)
    {
        Mail::to($event->user->email)->send(new UserRegisteredEmail($event->user));
    }
}
