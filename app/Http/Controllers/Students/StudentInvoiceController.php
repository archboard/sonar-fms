<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Factories\InvoiceFromRequestFactory;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceScholarship;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
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
                'currency',
                'student',
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return InvoiceResource::collection($invoices);
    }

    /**
     * Create a new invoice for a student
     *
     * @param Request $request
     * @param Student $student
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function create(Request $request, Student $student)
    {
        $title = __('Create a new invoice for :student', [
            'student' => $student->full_name,
        ]);
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
                'label' => __('New invoice'),
                'route' => route('students.invoices.create', $student),
            ],
        ];

        return inertia('invoices/Create', [
            'title' => $title,
            'breadcrumbs' => $breadcrumbs,
            'students' => [$student->id],
            'endpoint' => route('students.invoices.store', $student),
            'method' => 'post',
        ])->withViewData(compact('title'));
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
        InvoiceFromRequestFactory::make($request)
            ->build();

        session()->flash('success', __('Invoice created successfully.'));

        return redirect()->route('students.show', $student);
    }

    /**
     * Display the specified resource.
     *
     * @param Student $student
     * @param Invoice $invoice
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function show(Request $request, Student $student, Invoice $invoice)
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

        /** @var User $user */
        $user = $request->user();

        return inertia('invoices/Show', [
            'title' => $title,
            'invoice' => $invoice->toResource(),
            'student' => $student->toResource(),
            'breadcrumbs' => $breadcrumbs,
            'permissions' => [
                'invoices' => $user->getPermissions(Invoice::class),
                'students' => $user->getPermissions(Student::class),
            ],
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
