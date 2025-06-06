<?php

namespace App\Http\Controllers;

use App\Factories\InvoiceFromRequestFactory;
use App\Http\Requests\CreateInvoiceRequest;
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
            'students' => $user->studentSelections()->pluck('student_uuid'),
            'endpoint' => route('selection.invoices.store'),
            'method' => 'post',
        ])->withViewData(compact('title'));
    }

    /**
     * This stores the invoices that could be for the selection,
     * or changed on the actual invoice page
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CreateInvoiceRequest $request)
    {
        $invoices = InvoiceFromRequestFactory::make($request)
            ->build();

        return Invoice::successfullyCreatedResponse($invoices);
    }
}
