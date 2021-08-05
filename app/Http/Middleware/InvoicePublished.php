<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InvoicePublished
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
        $invoice = $request->route('invoice');

        if (optional($invoice)->published_at) {
            return $next($request);
        }

        session()->flash('error', __('This invoice is unavailable.'));

        return redirect()->route('invoices.index');
    }
}
