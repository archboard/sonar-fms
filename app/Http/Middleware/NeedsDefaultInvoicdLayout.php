<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NeedsDefaultInvoicdLayout
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
        if (
            $request->school()
                ->invoiceLayouts()
                ->default()
                ->exists()
        ) {
            return $next($request);
        }

        session()->flash('error', __('No default layout exists. Please set or create a default layout.'));

        return redirect()->route('layouts.index');
    }
}
