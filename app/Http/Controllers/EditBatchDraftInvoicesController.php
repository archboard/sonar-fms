<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class EditBatchDraftInvoicesController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Inertia\Response|\Inertia\ResponseFactory
     */
    public function __invoke(Request $request, string $batch)
    {
        $this->authorize('update', Invoice::class);

        $query = Invoice::batch($batch)
            ->unpublished();

        if ($query->count() === 0) {
            session()->flash('error', __('This batch has no unpublished invoices.'));
            return back();
        }

        /** @var Invoice $invoice */
        $invoice = $query->first();
        $title = __('Update invoice batch');
        $breadcrumbs = [
            [
                'label' => __('Invoices'),
                'route' => route('invoices.index'),
            ],
            [
                'label' => $invoice->title,
                'route' => route('invoices.index', ['batch_id' => $batch]),
            ],
            [
                'label' => __('Edit'),
                'route' => route('batches.edit', $batch),
            ],
        ];

        return inertia('invoices/Create', [
            'title' => $title,
            'breadcrumbs' => $breadcrumbs,
            'invoice' => $invoice->forEditing(true),
            'endpoint' => route('batches.update', $batch),
            'method' => 'put',
        ])->withViewData(compact('title'));
    }
}
