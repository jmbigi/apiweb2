<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    use HasFactory;
    protected $table = "webhook_log";

    protected $fillable = [
        'response',
        'plan_id',
        'subscription_id',
        'order_id',
        'event_type',
        'status',
        'user_id',
    ];
}
