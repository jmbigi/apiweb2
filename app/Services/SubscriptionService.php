<?php

namespace App\Services;

use App\Models\SubscriptionPlan;
use App\Models\SubscribedUser;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SubscriptionService
{
    public function getSubscriptionDetails(): array
    {
        $user = Auth::user();

        if (!$user) {
            return $this->formatSubscriptionDetails(null, null); // Usuario no autenticado, devolver detalles de suscripción nulos
        }

        if (!$user instanceof \App\Models\User) {
            return $this->formatSubscriptionDetails(null, null); // Usuario no autenticado, devolver detalles de suscripción nulos
        }

        $subscribed_user = $user->subscriptions()->where('user_id', $user->id)->first();;

        $subscription_plan = $subscribed_user ? SubscriptionPlan::find($subscribed_user->subscription_plan_id) : null;

        return $this->formatSubscriptionDetails($subscription_plan, $subscribed_user);
    }

    public function getSubscriptionDetailsById($userId): array
    {
        $user = User::find($userId);

        if (!$user) {
            return $this->formatSubscriptionDetails(null, null); // Usuario no autenticado, devolver detalles de suscripción nulos
        }

        if (!$user instanceof \App\Models\User) {
            return $this->formatSubscriptionDetails(null, null); // Usuario no autenticado, devolver detalles de suscripción nulos
        }

        $subscribed_user = $user->subscriptions()->where('user_id', $user->id)->first();;

        $subscription_plan = $subscribed_user ? SubscriptionPlan::find($subscribed_user->subscription_plan_id) : null;

        return $this->formatSubscriptionDetails($subscription_plan, $subscribed_user);
    }


    protected static function formatSubscriptionDetails(?SubscriptionPlan $subscription_plan, ?SubscribedUser $subscribed_user): array
    {
        $demoPeriod = false;
        $subscription_plan_name = $subscription_plan ? $subscription_plan->name : null;
        $isPaid = false;
        $isFav = false;
        $isAd = true;
        $annotation = 5;
        $level = 0;
        $is_paypal = false;
        $now = Carbon::now();
        $current_time = (clone $now)->format('H:i:s'); // Hora actual en formato HH:MM:SS
        //
        $next_month = (clone $now)->addDays(31);
        if ($demoPeriod) {
            if (mt_rand(1, 3) !== 1) {
                $next_month = (clone $now)->addMinutes(10);
            } else {
                $next_month = (clone $now)->subMinute(); // Restar un minuto a la fecha actual
            }
            $current_time = (clone $next_month)->format('H:i:s');
        }
        $expiration_datetime = $next_month;
        if ($subscription_plan) {
            $level = ($subscription_plan->type >= 0 && $subscription_plan->type <= 2) ? $subscription_plan->type : 0;
            if ($subscribed_user) {
                $is_paypal = $subscribed_user->paypal_subscription_id ? true : false;
                //
                if ($subscribed_user->subscription_end_date) {
                    $expiration_datetime = Carbon::parse($subscribed_user->subscription_end_date);
                    if ($expiration_datetime->hour == 0 && $expiration_datetime->minute == 0 && $expiration_datetime->second == 0) {
                        $expiration_datetime = Carbon::parse($expiration_datetime->toDateString() . ' ' . $current_time);
                    }    
                } else {
                    $expiration_datetime = $next_month;
                }
            }
            switch ($subscription_plan->type) {
                case 0:
                    break;
                case 1:
                    $isPaid = true;
                    $isFav = false; // cambiado a false, debes ser premium para esto
                    $isAd = false;
                    $annotation = 15;
                    break;
                case 2:
                    $isPaid = true;
                    $isFav = true;
                    $isAd = false;
                    $annotation = 'unlimited';
                    break;
                default:
                    break;
            }
        }
        $expired = $expiration_datetime < $now;

        return [
            'subscription_name' => $subscription_plan_name,
            'is_paid' => $isPaid,
            'is_advertisement' => $isAd,
            'is_favourite' => $isFav,
            'annotation_limit' => $annotation,
            'level' => $level,
            'is_paypal' => $is_paypal,
            'expiration_datetime' => $expiration_datetime,
            'expired' => $expired,
            'now' => $now,
            'next_month' => $next_month,
        ];
    }

    public function getSubscriptionByType(int $type): ?SubscriptionPlan
    {
        $now = Carbon::now();
        // Busca plan por tipo 0, 1, 2
        return SubscriptionPlan::where('type', $type)
            ->where('status', 1) // Condición para el estado activo
            ->where(function ($query) use ($now) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $now);
            })
            ->orderBy('start_date', 'desc')
            ->first();
    }

    public function updateSubscription(int $planType)
    {
        $demoPeriod = false;
        $user = Auth::user();
        // Valida usuario
        if (!$user instanceof \App\Models\User) {
            return false;
        }
        $now = Carbon::now();
        $next_month = (clone $now)->addDays(31);
        if ($demoPeriod) {
            $next_month = (clone $now)->addMinutes(10);
        }
        // Valida tipo plan == -1
        if ($planType == -1) {
            $planType = 0;
        }
        // Busca plan por tipo
        $subscriptionPlan = $this->getSubscriptionByType($planType);
        if (!$subscriptionPlan) {
            return false;
        }
        //
        $plan_count = 0;
        while ($plan_count < 3) {
            // Elimina registro de suscripcion actual
            $subscribed_user = $user->subscriptions()->where('user_id', $user->id)->first();
            if ($subscribed_user) {
                $subscribed_user->delete();
                $plan_count = $plan_count + 1;
            } else {
                break;
            }
        }
        // Agrega registro de suscripcion nuevo
        $subscribed_user = new SubscribedUser();
        $subscribed_user->user_id = $user->id;
        $subscribed_user->subscription_plan_id = $subscriptionPlan->id;
        $subscribed_user->subscription_end_date = ($planType == 0) ? null : $next_month;
        $subscribed_user->save();
        // Retorna resultado       
        return true;
    }

    public function updateSubscriptionById($id, int $planType)
    {
        $demoPeriod = false;
        $user = User::find($id);

        // Valida usuario
        if (!$user instanceof \App\Models\User) {
            return false;
        }
        $now = Carbon::now();
        $next_month = (clone $now)->addDays(31);
        if ($demoPeriod) {
            $next_month = (clone $now)->addMinutes(10);
        }
        // Valida tipo plan == -1
        if ($planType == -1) {
            $planType = 0;
        }
        // Busca plan por tipo
        $subscriptionPlan = $this->getSubscriptionByType($planType);
        if (!$subscriptionPlan) {
            return false;
        }
        //
        $plan_count = 0;
        while ($plan_count < 3) {
            // Elimina registro de suscripcion actual
            $subscribed_user = $user->subscriptions()->where('user_id', $user->id)->first();
            if ($subscribed_user) {
                $subscribed_user->delete();
                $plan_count = $plan_count + 1;
            } else {
                break;
            }
        }
        // Agrega registro de suscripcion nuevo
        $subscribed_user = new SubscribedUser();
        $subscribed_user->user_id = $user->id;
        $subscribed_user->subscription_plan_id = $subscriptionPlan->id;
        $subscribed_user->subscription_end_date = ($planType == 0) ? null : $next_month;
        $subscribed_user->save();
        // Retorna resultado       
        return true;
    }

}
