<?php

namespace App\Http\Controllers;

use App\Models\PaymentImport;
use App\Traits\SendsApiResponses;
use Illuminate\Http\Request;

class ConvertPaymentImportMappingToTemplateController extends Controller
{
    use SendsApiResponses;

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, PaymentImport $import)
    {
        $this->authorize('create', PaymentImport::class);

        $data = $request->validate([
            'name' => ['required'],
        ]);

        $request->user()
            ->paymentImportTemplates()
            ->create([
                'school_id' => $import->school_id,
                'name' => $data['name'],
                'template' => $import->mapping,
            ]);

        session()->flash('success', __('Template created successfully.'));

        return back();
    }
}
