<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $table = "order";

    protected $fillable = [
        'userId',
        'subscription_plan_id',
        'orderId',
        'paymentSource',
        'subscriptionID',
        'paypal_plan_id',
        'paypal_plan_name',
        'paypal_plan_price',
        'message',
    ];
}
