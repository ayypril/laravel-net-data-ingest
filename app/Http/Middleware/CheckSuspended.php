<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckSuspended
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * Checks if a user is marked as suspended in the database, and if so, immediately log them out
     * and show them a message saying their account has been suspended.
     */
    public function handle($request, Closure $next)
    {
        /*
         * This is very similar to CheckIfAdmin. The same general idea applies.
         * First see if the user is logged in,
         */
        if (Auth::check()) {
            if (Auth::user()->suspended !== 1) {
                return $next($request);
            } else {

                return response('<html><h1>Error: Your account has been suspended.
                </h1><p>Click <a href="/">here</a> to return to the home page.</p></html>', 403);
            }
        } else {
            // user is not logged in
            return $next($request);
        }
    }
}
