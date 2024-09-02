<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(Auth::check()) {
            // admin
            if(Auth::user()->type == 1) {
                return $next($request);
            } else {
                // user
                $account = Auth::user()->account;
                if(!empty($account)) {
                    return redirect()->route('branches', encrypt($account->id));
                }
            }
        }

        // not logged in
        return redirect('/login');
    }
}
