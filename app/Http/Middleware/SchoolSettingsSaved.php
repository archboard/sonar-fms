<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SchoolSettingsSaved
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
        $school = $request->school();

        if ($school->currency_id && $school->timezone) {
            return $next($request);
        }

        session()->flash('error', __('Please set currency and timezone'));

        return redirect()->route('settings.school');
    }
}
