<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Hash;
use App\Models\Account;
use App\Models\User;
use Auth;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $user = User::where('email', $googleUser->email)->first();

            if (!$user) {
                $email_arr = explode('@', $googleUser->email);
                $first_part = reset($email_arr);
                $password = Hash::make($first_part.'123!');

                $user = User::create([
                    'account_id' => 1,
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'username' => $first_part,
                    'password' => $password, // Generate random password
                    'google_id' => $googleUser->id,
                    'type' => 1,
                ]);

                $user->assignRole('superadmin');
            }

            Auth::login($user);

            return redirect()->route('home'); // Redirect to dashboard
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Something went wrong.');
        }
    }
}
