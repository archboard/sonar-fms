<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvoicePaymentResource;
use App\Models\InvoicePayment;
use Illuminate\Http\Request;

class ListInvoicePaymentController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function __invoke(Request $request)
    {
        $this->authorize('viewAny', InvoicePayment::class);

        $title = __('Payments');
        $payments = InvoicePayment::filter($request->all())
            ->with('invoice', 'currency')
            ->paginate($request->input('perPage', 25));

        return inertia('payments/Index', [
            'title' => $title,
            'payments' => InvoicePaymentResource::collection($payments),
        ])->withViewData(compact('title'));
    }
}
