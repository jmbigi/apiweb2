<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubscribedUser extends Model
{
    use HasFactory;
    protected $table = 'subscribed_user';
    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'paypal_subscription_id',
        'subscription_end_date'
    ];
    
    public function subscriptionPlan() {
        return $this->belongsTo(SubscriptionPlan::class);
    }
    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'paypal_plan_id', 'plan_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
