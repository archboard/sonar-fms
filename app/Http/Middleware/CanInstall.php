<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class CanInstall
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
        /** @var User $user */
        $user = $request->user();
        $tenant = Tenant::firstOrNew(['domain' => $request->getHost()]);

        // Authorize if there is a user and don't have permission
        // This permission doesn't exist anywhere, so only someone
        // who manages the tenancy will pass this gate
        if ($user && $user->cant('install tenant')) {
            abort(403);
        }

        // Or there isn't a user and the tenant has users
        if ($tenant->hasBeenInstalled()) {
            abort(404);
        }

        return $next($request);
    }
}
