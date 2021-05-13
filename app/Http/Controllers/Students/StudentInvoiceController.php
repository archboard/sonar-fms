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
use Illuminate\Support\Facades\DB;

class StudentInvoiceController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Invoice::class, 'invoice');
    }

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

            $fees = $school->fees->keyBy('id');
            $invoiceItems = collect($data['items'])
                ->map(fn (array $item) => InvoiceItem::generateAttributesForInsert(
                    $invoiceAttributes['uuid'],
                    $item,
                    $fees
                ));

            DB::table('invoice_items')
                ->insert($invoiceItems->toArray());

            if (!empty($data['scholarships'])) {
                $scholarships = $school->scholarships->keyBy('id');
                $scholarshipItems = collect($data['scholarships'])
                    ->map(fn (array $item) => InvoiceScholarship::generateAttributesForInsert(
                        $invoiceAttributes['uuid'],
                        $item,
                        $invoice->amount_due,
                        $scholarships
                    ));

                DB::table('invoice_scholarships')
                    ->insert($scholarshipItems->toArray());

                $invoice->setAmountDue()
                    ->save();
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
     * @param Student $student
     * @param Invoice $invoice
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function show(Student $student, Invoice $invoice)
    {
        $title = $invoice->title;
        $invoice->fullLoad();
        $student->load('users');

        $breadcrumbs = [
            [
                'label' => __('Students'),
                'route' => route('students.index'),
            ],
            [
                'label' => $student->full_name,
                'route' => route('students.show', $student),
            ],
            [
                'label' => $invoice->title,
                'route' => route('students.invoices.show', [$student, $invoice]),
            ],
        ];

        return inertia('invoices/Show', [
            'title' => $title,
            'invoice' => $invoice->toResource(),
            'student' => $student->toResource(),
            'breadcrumbs' => $breadcrumbs,
            'permissions' => auth()->user()->getPermissions(Invoice::class),
        ])->withViewData(compact('title'));
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
