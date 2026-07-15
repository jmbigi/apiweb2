<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
class CheckCustomAccessToken
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        if($user){
            $token = DB::table('personal_access_tokens')
            ->where('tokenable_type', 'App\Models\User') // Adjust the model namespace if needed
            ->where('tokenable_id', $user->id)
            ->where('name', 'custom-token-name')
            ->first();
            // dd($token);
           
            if ($user->status === 0) {
                Auth::guard('web')->logout();
                return redirect()->route('login')->with('error', 'Your account has been suspended.');
            }
            if ($token && $token->expires_at < Carbon::now()) {
                DB::table('personal_access_tokens')
                    ->where('tokenable_type', 'App\Models\User')
                    ->where('tokenable_id', $user->id)
                    ->where('name', 'custom-token-name')
                    ->delete();
    
                    Auth::guard('web')->logout();
    
           
    
                return redirect()->route('login')->with('error', 'Session expired. Please log in again.');
            }
            elseif(!$token){
                Auth::guard('web')->logout();
                return redirect()->route('login')->with('error', 'Session expired. Please log in again.');
            }
        }else {
            return $next($request);
        }

        return $next($request);
    }
}