<?php

namespace App\Http\Controllers;

use App\Factories\InvoiceFromRequestFactory;
use App\Http\Requests\CreateInvoiceRequest;
use App\Http\Resources\StudentResource;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;

class StudentSelectionInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('create', Invoice::class);

        $title = __('Create invoice for student selection');
        $breadcrumbs = [
            [
                'label' => __('Invoices'),
                'route' => route('invoices.index'),
            ],
            [
                'label' => __('Invoices for selection'),
                'route' => route('selection.invoices.create'),
            ],
        ];
        /** @var User $user */
        $user = $request->user();

        return inertia('invoices/Create', [
            'title' => $title,
            'breadcrumbs' => $breadcrumbs,
            'students' => $user->selected_students->pluck('id'),
        ])->withViewData(compact('title'));
    }

    /**
     * This stores the invoices that could be for the selection,
     * or changed on the actual invoice page
     *
     * @param CreateInvoiceRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CreateInvoiceRequest $request)
    {
        $invoices = InvoiceFromRequestFactory::make($request)
            ->build();

        session()->flash('success', __(':count invoices created successfully.', [
            'count' => $invoices->count(),
        ]));

        return redirect()->route('students.index');
    }
}
