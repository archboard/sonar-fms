<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceSearchController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function __invoke(Request $request)
    {
        $this->authorize('viewAny', Invoice::class);

        $invoices = Invoice::notAChild()
            ->with([
                'student',
                'students',
                'currency',
            ])
            ->filter($request->all())
            ->limit(25);

        return InvoiceResource::collection($invoices);
    }
}
