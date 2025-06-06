<?php

namespace App\Http\Controllers;

use App\Factories\CombineInvoiceFactory;
use App\Http\Requests\CombineInvoicesRequest;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\UserResource;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CombineInvoiceController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\RedirectResponse|\Inertia\Response|\Inertia\ResponseFactory
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('create', Invoice::class);

        /** @var User $user */
        $user = $request->user();
        $title = __('Combine invoices');
        $selection = $user->selectedInvoices()
            ->with('student', 'currency')
            ->get();

        if ($selection->count() < 2) {
            session()->flash('error', __("You don't have enough invoices selected to combine."));

            return redirect()->route('invoices.index');
        }

        return inertia('invoices/Combine', [
            'title' => $title,
            'selection' => InvoiceResource::collection($selection),
            'suggestedUsers' => UserResource::collection($user->getSelectionSuggestedUsers()),
            'endpoint' => '/combine',
            'method' => 'post',
        ])->withViewData(compact('title'));
    }

    /**
     * Combines the user's selection into a single invoice
     * that contains the selection as children invoices
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CombineInvoicesRequest $request)
    {
        $results = CombineInvoiceFactory::make($request)
            ->build();

        $request->user()->invoiceSelections()->delete();

        session()->flash('success', __('Invoices combined successfully.'));

        return redirect()->route('invoices.show', $results->first());
    }

    /**
     * Edit a parent invoice
     *
     * @return \Illuminate\Http\RedirectResponse|\Inertia\Response|\Inertia\ResponseFactory
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        if ($invoice->published_at) {
            session()->flash('error', __('This invoice is already published.'));

            return redirect()->route('invoices.show', $invoice);
        }

        $selection = $invoice->children()
            ->with('student', 'currency')
            ->get();

        if ($selection->isEmpty()) {
            return redirect()->route('invoices.edit', $invoice);
        }

        $title = __('Edit combined invoice :invoice_number', [
            'invoice_number' => $invoice->invoice_number,
        ]);

        $invoice->load(
            'invoicePaymentSchedules',
            'invoicePaymentSchedules.invoicePaymentTerms'
        );
        $assignedUsers = $invoice->users()->pluck('uuid');
        $suggestedUsers = User::whereHas('students', function (Builder $builder) use ($selection) {
            $builder->whereIn('students.uuid', $selection->pluck('student_uuid'));
        })
            ->orWhereIn('uuid', $invoice->users()->pluck('uuid'))
            ->orderBy('last_name')
            ->get();

        return inertia('invoices/Combine', [
            'title' => $title,
            'invoice' => $invoice->toResource(),
            'assignedUsers' => $assignedUsers,
            'selection' => InvoiceResource::collection($selection),
            'suggestedUsers' => UserResource::collection($suggestedUsers),
            'endpoint' => "/combine/{$invoice->uuid}",
            'method' => 'put',
        ])->withViewData(compact('title'));
    }

    public function update(CombineInvoicesRequest $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $results = CombineInvoiceFactory::make($request, $invoice)
            ->build();

        session()->flash('success', __('Invoice updated successfully.'));

        return redirect()->route('invoices.show', $results->first());
    }
}
