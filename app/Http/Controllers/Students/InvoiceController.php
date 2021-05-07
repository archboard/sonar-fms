<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\InvoiceItem;
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

            /** @var Invoice $invoice */
            $invoice = Invoice::create($invoiceAttributes);
            DB::table('invoice_items')
                ->insert(InvoiceItem::generateAttributesForInsert(
                    $invoiceAttributes['uuid'],
                    collect($request->validated()['items']),
                    $request->school()->fees->keyBy('id')
                ));

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
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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
