<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckActive
{

    protected function respondError($message, $err_status = 401)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
        ], $err_status);
    }

    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        if ($user) {

            if ($user->status === 0) {
                return self::respondError('Your account has been suspended.');
            }
        } else {
            return self::respondError('Invalid session. Please log in again.');
        }

        return $next($request);
    }
}