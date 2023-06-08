<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use Cache;
use Illuminate\Http\Request;
use App\Models\User;

class UpdateLastActivityTime
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

            $expiresAt = now()->addMinutes(2); /* keep online for 2 min */
            Cache::put('user-is-online-' . Auth::user()->id, true, $expiresAt);

            /* last activity */
            Auth::user()->update(['last_activity_time' => now()]);
        }

        return $next($request);
    }
}
