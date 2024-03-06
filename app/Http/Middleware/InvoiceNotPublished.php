<?php

namespace App\Http\Middleware;

use App\Models\Invoice;
use Closure;
use Illuminate\Http\Request;

class InvoiceNotPublished
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        /** @var Invoice|null $invoice */
        $invoice = $request->route('invoice');

        if ($invoice && ! $invoice->published_at) {
            return $next($request);
        }

        session()->flash('error', __('This invoice has already been published.'));

        return redirect()->route('invoices.index');
    }
}
