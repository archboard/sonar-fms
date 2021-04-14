<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ScopeBouncer
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
        \Bouncer::scope()->to($request->school()->id);

        return $next($request);
    }
}
