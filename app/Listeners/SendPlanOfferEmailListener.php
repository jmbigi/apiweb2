<?php

namespace App\Listeners;

use App\Mail\PlanOfferEmail;
use Illuminate\Support\Facades\Mail;

class SendPlanOfferEmailListener
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(object $event)
    {
        Mail::to($event->user->email)->send(new PlanOfferEmail($event->user));
    }
}
