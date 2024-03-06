<?php

namespace App\Http\Controllers;

use App\Models\InvoiceImport;
use Illuminate\Http\Request;

class ConvertInvoiceImportMappingToTemplateController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, InvoiceImport $import)
    {
        $this->authorize('create', InvoiceImport::class);

        $data = $request->validate(['name' => ['required']]);

        $request->user()
            ->invoiceTemplates()
            ->create([
                'school_id' => $import->school_id,
                'name' => $data['name'],
                'template' => $import->mapping,
                'for_import' => true,
            ]);

        session()->flash('success', __('Template created successfully.'));

        return back();
    }
}
