<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AllowsOidcLogins
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->tenant()->allow_oidc_login) {
            return $next($request);
        }

        abort(404);
    }
}
