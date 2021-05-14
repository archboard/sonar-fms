<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class SetLocale
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

        $locale = $user
            ? $user->locale
            : session('locale', $request->getPreferredLanguage(config('app.locales')));

        session(compact('locale'));
        app()->setLocale($locale);

        return $next($request);
    }
}
