<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceTemplate;
use App\Models\School;
use Illuminate\Http\Request;

class ConvertInvoiceToTemplateController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, School $school, Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        $this->authorize('create', InvoiceTemplate::class);

        $data = $request->validate([
            'name' => 'required|max:255',
        ]);

        $invoice->convertToInvoiceTemplate($data);

        session()->flash('success', __('Invoice template created successfully.'));

        return back();
    }
}
