<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;

class MyInvoiceController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $title = __('My invoices');
        $invoices = Invoice::leftJoin('invoice_user', function (JoinClause $join) use ($user) {
                $join->on('invoices.uuid', '=', 'invoice_user.invoice_uuid');
            })
            ->where(function (Builder $builder) use ($user) {
                $builder->where('invoice_user.user_uuid', $user->uuid)
                    ->orWhereIn('student_uuid', $user->students->pluck('uuid'));
            })
            ->published()
            ->with('student', 'currency')
            ->filter($request->all())
            ->paginate($request->input('perPage', 25))
            ->withQueryString();

        return inertia('invoices/Index', [
            'title' => $title,
            'invoices' => InvoiceResource::collection($invoices),
            'permissions' => [
                'students' => ['view' => true],
            ],
            'endpoint' => route('my-invoices.index'),
            'canSelect' => false,
        ])->withViewData(compact('title'));
    }
}
