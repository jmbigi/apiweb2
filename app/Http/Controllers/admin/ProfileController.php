<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

use App\Models\User;

use Illuminate\Validation\Rule;
class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }
    public function change_password(Request $request): View
    {
        return view('profile.change_passoword', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // $request->user()->fill($request->validated());
        $country_code = $request->input('country_code');
        if (!isset($country_code)) {                       
            $country_code = '34';
        }
        $user = $request->user();
        $data = $request->validated();

        // Check if the telephone field is provided in the request
        if (isset($data['telephone'])) {
            $data['telephone'] = '(+'.$country_code.')'.$data['telephone'];
            
                $existingUser = User::where('telephone', $data['telephone'])->where('id', '!=', $user->id)->first();
                if ($existingUser) {
                    return redirect()->back()->withErrors(['telephone' => 'The telephone number is not unique.'])->withInput();
                }
            
        }
        $user->fill($data);
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
        $user->save();
        // if ($request->user()->isDirty('email')) {
        //     $request->user()->email_verified_at = null;
        // }
        // $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
