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
        $this->authorize('viewAny', InvoiceRefund::class);

        ray()->showQueries();
        $refunds = InvoiceRefund::whereHas('invoice', function (Builder $builder) use ($invoice) {
                $builder->select('uuid')
                    ->where('invoices.parent_uuid', $invoice);
            })
            ->with([
                'invoice',
                'currency',
                'user',
            ])
            ->get();
        ray()->stopShowingQueries();

        return InvoiceRefund::resource($refunds);
    }
}
