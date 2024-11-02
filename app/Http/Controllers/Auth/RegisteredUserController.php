<?php

namespace App\Http\Controllers\Auth;

use App\Models\Role;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Models\SubscribedUser;
use Illuminate\Validation\Rules;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;

use Illuminate\Auth\Events\Registered;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $subscription_plan = SubscriptionPlan::where('name','Free')->orWhere('name','free')->first();
        $endDate = Carbon::now()->addMonth();
        SubscribedUser::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $subscription_plan->id
        ]);
        $defaultRole = Role::where('name', 'superadmin')->first();
        // dd($defaultRole);
        $user->attachRole($defaultRole);

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}
