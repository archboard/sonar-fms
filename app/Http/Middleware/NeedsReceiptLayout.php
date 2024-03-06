<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NeedsReceiptLayout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (
            $request->school()
                ->receiptLayouts()
                ->default()
                ->exists()
        ) {
            return $next($request);
        }

        session()->flash('error', __('No default layout exists. Please set or create a default layout.'));

        return redirect()->route('receipt-layouts.index');
    }
}
