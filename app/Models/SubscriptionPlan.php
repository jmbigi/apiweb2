<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;
    protected $table = "subscription_plan";

    protected $fillable = [
        'name',
        'description',
        'price',
        'start_date',
        'end_date',
        'status',
        'plan_id',
        'type',
        'type_label',
    ];

    public function subscribers()
    {
        return $this->hasMany(SubscribedUser::class, 'subscription_plan_id');
    }
    const FREE = 0;
    const BASIC = 1;
    const PREMIUM = 2;

    public function getTypeLabelAttribute()
    {
        $labels = [
            self::FREE => 'FREE',
            self::BASIC => 'BASIC',
            self::PREMIUM => 'PREMIUM',
        ];

        return $labels[$this->type];
    }
}
