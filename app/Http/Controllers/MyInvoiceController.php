<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;

class MyInvoiceController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $title = __('My invoices');
        $invoices = Invoice::forUser($user)
            ->published()
            ->with('student', 'currency')
            ->filter($request->all())
            ->paginate($request->input('perPage', 25))
            ->withQueryString();

        return inertia('my-invoices/Index', [
            'title' => $title,
            'invoices' => InvoiceResource::collection($invoices),
            'endpoint' => route('my-invoices.index'),
        ])->withViewData(compact('title'));
    }

    public function show(Request $request, Invoice $invoice)
    {
        $this->authorize('view invoice', $invoice);

        $title = $invoice->title . ': ' . $invoice->invoice_number;
        $invoice->fullLoad()
            ->loadChildren();
        $user = $request->user();

        return inertia('my-invoices/Show', [
            'title' => $title,
            'invoice' => $invoice->toResource(),
            'permissions' => [
                'invoices' => [
                    'parent' => $invoice->parent
                        ? $user->can('view invoice', $invoice->parent)
                        : false,
                ],
                'students' => [
                    'view' => $user->can('view', $invoice->student),
                ],
                'payments' => [
                    'view' => true,
                ],
                'refunds' => [
                    'view' => true,
                ],
            ],
        ])->withViewData(compact('title'));
    }
}
