<?php

namespace App\Http\Controllers\Students;

use App\Factories\InvoiceFromRequestFactory;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;

class StudentInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request, Student $student)
    {
        $this->authorize('view', $student);
        /** @var User $user */
        $user = $request->user();

        $query = $student->invoices()
            ->with([
                'currency',
                'parent',
            ])
            ->orderBy('created_at', 'desc');

        // Prevent certain users from seeing draft invoices
        if ($user->cant('view', Invoice::class)) {
            $query->published();
        }

        $invoices = $query
            ->paginate(10)
            ->withQueryString();

        return InvoiceResource::collection($invoices);
    }

    /**
     * Create a new invoice for a student
     *
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function create(Request $request, Student $student)
    {
        $this->authorize('create', Invoice::class);

        $title = __('Create a new invoice');
        $breadcrumbs = [
            $this->makeBreadcrumb(__('Students'), route('students.index')),
            $this->makeBreadcrumb($student->full_name, route('students.show', $student)),
            $this->makeBreadcrumb(__('New invoice'), route('students.invoices.create', $student)),
        ];

        return inertia('invoices/Create', [
            'title' => $title,
            'breadcrumbs' => $breadcrumbs,
            'students' => [$student->uuid],
            'endpoint' => route('students.invoices.store', $student),
            'method' => 'post',
        ])->withViewData(compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CreateInvoiceRequest $request, Student $student)
    {
        $this->authorize('create', Invoice::class);

        InvoiceFromRequestFactory::make($request)
            ->build();

        session()->flash('success', __('Invoice created successfully.'));

        return redirect()->route('students.show', $student);
    }

    /**
     * Display the specified resource.
     *
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function show(Request $request, Student $student, Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $title = $invoice->title;
        $invoice->fullLoad();
        $student->load('users');

        $breadcrumbs = [
            $this->makeBreadcrumb(__('Students'), route('students.index')),
            $this->makeBreadcrumb($student->full_name, route('students.show', $student)),
            $this->makeBreadcrumb($invoice->title, route('students.invoices.show', [$student, $invoice])),
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
}
