<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ManagesTenancy
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()->manages_tenancy) {
            return $next($request);
        }

        abort(403);
    }
}
