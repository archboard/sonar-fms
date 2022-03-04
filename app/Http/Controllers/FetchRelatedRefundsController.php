<?php

namespace App\Http\Controllers;

use App\Models\InvoiceRefund;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FetchRelatedRefundsController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function __invoke(Request $request, string $invoice)
    {
        $this->authorize('view', InvoiceRefund::class);

        $refunds = InvoiceRefund::whereHas('invoice', function (Builder $builder) use ($invoice) {
                $builder->select('uuid')
                    ->where('invoices.parent_uuid', $invoice);
            })
            ->orderBy('refunded_at', 'desc')
            ->with([
                'invoice',
                'currency',
                'user',
            ])
            ->get();

        return InvoiceRefund::resource($refunds);
    }
}
