<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\Account;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function username() {
        return 'username';
    }

    // custom login
    public function login(Request $request) {

        $request->validate([
            'username' => 'required',
            'password' => 'required',
            'account_code' => 'required',
            'account_password' => 'required'
        ]);

        $user_credentials = $request->only('username', 'password');
        $account_credentials = $request->only('account_code', 'account_password');

        $user = User::where('username', $request->username)->first();
        if(!empty($user)) {
            if (Hash::check($request->password, $user->password)) {
                // check if account was assigned to user
                Auth::attempt($user_credentials);
                // if(auth()->user()->hasRole('superadmin')) {
                //     return redirect()->intended($this->redirectPath());
                // }

                $account = Account::where('account_code', $request->account_code)->first();
                if (!empty($account)) {
                    if (Hash::check($request->account_password, $account->account_password)) {
                        // Authentication passed for both user and account
                        $assigned = auth()->user()->accounts()->where('id', $account->id)->first();
                        if(!empty($assigned)) {
                            auth()->user()->update([
                                'account_id' => $account->id
                            ]);

                            return redirect()->intended($this->redirectPath()); // Redirect to intended page
                        }

                        return back()->withInput($request->only('account_code'))->withErrors(['account_code' => 'Account is not assigned to the user']);
                    } else {
                        // Invalid account password
                        return back()->withInput($request->only('account_code'))->withErrors(['account_password' => 'Invalid password']);
                    }
                } else {
                    // Account not found
                    return back()->withInput($request->only('account_code'))->withErrors(['account_code' => 'Account code not found!']);
                }
            }
        }

        // Authentication failed for user
        return $this->sendFailedLoginResponse($request);
    }
}
