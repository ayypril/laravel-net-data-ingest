<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckUnverified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            if (Auth::user()->verified == 1) {
                return $next($request);
            } else {
                return response('Error: Your account is unverified and ineglible to perform this action.', 403)
                    ->header('Content-Type', 'text/plain');
            }
        } else {
            // user is not logged in
            return $next($request);
        }    }
}
