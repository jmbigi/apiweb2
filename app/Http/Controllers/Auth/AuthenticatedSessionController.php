<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;


class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        // Generate a token (you can use any logic you prefer)
        $token = Str::random(80); // Generate a random string as the token
        
        $existingToken = DB::table('personal_access_tokens')
        ->where('tokenable_type', 'App\Models\User')
        ->where('tokenable_id', $user->id)
        ->where('name','custom-token-name')
        ->first();
        //dd($existingToken);

        if($existingToken){
            DB::table('personal_access_tokens')->where('tokenable_id',$user->id)->where('name','custom-token-name')->update([
                'tokenable_type' => 'App\Models\User', // Adjust the model namespace if needed
                'tokenable_id' => $user->id,
                'name' => 'custom-token-name',
                'token' => hash('sha256', $token),
                'created_at' => now(),
                'expires_at' => now()->addMinutes(60), // Set token expiration time
            ]);
        }else{
            // Store the token in the personal_access_tokens table
       
            DB::table('personal_access_tokens')->insert([
                'tokenable_type' => 'App\Models\User', // Adjust the model namespace if needed
                'tokenable_id' => $user->id,
                'name' => 'custom-token-name',
                'token' => hash('sha256', $token),
                'created_at' => now(),
                'expires_at' => now()->addMinutes(60), // Set token expiration time
            ]);
        }

        
      

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
