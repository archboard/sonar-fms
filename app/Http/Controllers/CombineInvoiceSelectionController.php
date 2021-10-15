<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvoiceResource;
use App\Http\Resources\UserResource;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CombineInvoiceSelectionController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Inertia\Response|\Inertia\ResponseFactory
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

        $suggestedUsers = User::whereHas('students', function (Builder $builder) use ($selection) {
                $builder->whereIn('students.id', $selection->pluck('student_id'));
            })
            ->get();

        return inertia('invoices/Combine', [
            'title' => $title,
            'selection' => InvoiceResource::collection($selection),
            'suggestedUsers' => UserResource::collection($suggestedUsers),
        ])->withViewData(compact('title'));
    }
}
