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
            'invoices' => [
                'class' => Invoice::class,
                'authorize' => fn () => true,
            ],
            'payments' => [
                'class' => InvoicePayment::class,
                'authorize' => fn () => $this->authorize('view', InvoicePayment::class),
            ],
            'students' => [
                'class' => Student::class,
                'authorize' => fn () => $this->authorize('view', Student::class),
            ],
        ];
        $model = $configuration[$type] ?? null;

        if (! $model) {
            session()->flash('error', __('Export unavailable.'));

            return back();
        }

        $model['authorize']();

        $data = $request->validated();
        $data['school_id'] = $request->school()->id;
        $data['user_uuid'] = $request->user()->uuid;
        $data['model'] = $model['class'];

        $export = RecordExport::create($data);

        return Inertia::location(route('exports.download', $export));
    }
}
