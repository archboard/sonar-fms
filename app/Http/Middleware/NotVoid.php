<?php

namespace App\Http\Middleware;

use App\Models\Invoice;
use Closure;
use Illuminate\Http\Request;

class NotVoid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        /** @var Invoice|null $invoice */
        $invoice = $request->route('invoice');

        if ($invoice?->voided_at) {
            session()->flash('error', __('Invoice has been voided.'));
            return back();
        }

        return $next($request);
    }
}
