<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvoiceResource;
use App\Models\School;
use App\Models\User;
use App\Traits\SendsApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InvoiceSelectionController extends Controller
{
    use SendsApiResponses;

    public function index(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        return InvoiceResource::collection(
            $user->selectedInvoices
        );
    }

    public function store(Request $request, School $school)
    {
        /** @var User $user */
        $user = $request->user();

        /** @var Collection $selection */
        $selection = $school->invoices()
            ->filter($request->all())
            ->pluck('uuid')
            ->map(fn ($invoice) => [
                'school_id' => $school->id,
                'user_uuid' => $user->id,
                'invoice_uuid' => $invoice,
            ]);

        if ($selection->isNotEmpty()) {
            $user->invoiceSelections()->delete();
            DB::table('invoice_selections')->insert($selection->toArray());
        }

        session()->flash('success', __('Selected :count invoices', [
            'count' => $selection->count()
        ]));

        return back();
    }

    public function update(Request $request, string $uuid)
    {
        /** @var User $user */
        $user = $request->user();

        $user->selectInvoice($uuid);

        return response()->json();
    }

    public function destroy(Request $request, string $uuid)
    {
        /** @var User $user */
        $user = $request->user();

        $user->invoiceSelections()
            ->invoice($uuid)
            ->delete();

        return response()->json();
    }
}
