<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecordsExportRequest;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\RecordExport;
use App\Models\Student;
use Inertia\Inertia;

class ExportRecordsController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(RecordsExportRequest $request, string $type)
    {
        $configuration = [
            'invoices' => Invoice::class,
            'payments' => InvoicePayment::class,
            'students' => Student::class,
        ];
        $model = $configuration[$type] ?? null;

        if (!$model) {
            session()->flash('error', __('Export unavailable.'));
            return back();
        }

        $this->authorize('viewAny', $model);

        $data = $request->validated();
        $data['school_id'] = $request->school()->id;
        $data['user_uuid'] = $request->user()->uuid;
        $data['model'] = $model;

        $export = RecordExport::create($data);

        return Inertia::location(route('exports.download', $export));
    }
}
