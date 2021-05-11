<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceScholarship;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Student $student)
    {
        $invoices = $student->invoices()
            ->with([
                'invoiceItems',
                'invoiceScholarships',
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return InvoiceResource::collection($invoices);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateInvoiceRequest $request
     * @param Student $student
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CreateInvoiceRequest $request, Student $student)
    {
        DB::transaction(function () use ($request, $student) {
            $invoiceAttributes = Invoice::getAttributesFromRequest($request, $student);
            $data = $request->validated();
            $school = $request->school();

            /** @var Invoice $invoice */
            $invoice = Invoice::create($invoiceAttributes);
            DB::table('invoice_items')
                ->insert(InvoiceItem::generateAttributesForInsert(
                    $invoiceAttributes['uuid'],
                    collect($data['items']),
                    $school->fees->keyBy('id')
                ));

            if (!empty($data['scholarships'])) {
                DB::table('invoice_scholarships')
                    ->insert(
                        InvoiceScholarship::generateAttributesForInsert(
                            $invoiceAttributes['uuid'],
                            collect($data['scholarships']),
                            $invoice->amount_due,
                            $school->scholarships->keyBy('id')
                        )
                    );

                $invoice->setAmountDue();
            }

            // Trigger the notification if it is set to queue
            if ($invoiceAttributes['notify']) {
                $invoice->notifyLater();
            }
        });

        session()->flash('success', __('Invoice created successfully.'));

        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateInvoiceRequest $request
     * @param Student $student
     * @param Invoice $invoice
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateInvoiceRequest $request, Student $student, Invoice $invoice)
    {
        $invoice->updateFromRequest($request);

        session()->flash('success', __('Invoice updated successfully.'));

        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
