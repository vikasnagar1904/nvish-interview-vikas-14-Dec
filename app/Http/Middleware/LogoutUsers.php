<?php

namespace App\Http\Middleware;

use Auth;
use Closure;

class LogoutUsers
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
        $user = Auth::user();

        if (!empty($user)) {
            if ($user->login_status == '0') {
                Auth::logout();

                return redirect()->route('login.show');
            }
        }

        return $next($request);
    }
}