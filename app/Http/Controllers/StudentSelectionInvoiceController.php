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
        $students = $request->user()
            ->studentSelections;

        return inertia('invoices/Create', [
            'title' => $title,
            'students' => StudentResource::collection($students),
        ])->withViewData(compact('title'));
    }

    public function store(CreateInvoiceRequest $request)
    {
        /** @var User $user */
        $user = $request->user()
            ->load('studentSelections', 'studentSelections.student');

        $invoices = InvoiceFromRequestFactory::make(
            $request,
            $user->studentSelections->map->student
        )->build();

        session()->flash('success', __(':count invoices created successfully.', [
            'count' => $invoices->count(),
        ]));

        return redirect()->route('students.index');
    }
}
