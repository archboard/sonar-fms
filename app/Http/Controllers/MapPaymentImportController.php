<?php

namespace App\Http\Controllers;

use App\Models\PaymentImport;
use Illuminate\Http\Request;

class MapPaymentImportController extends Controller
{
    public function index(Request $request, PaymentImport $import)
    {
        $this->authorize('update', PaymentImport::class);

        $title = __('Map Payment Import');
        $breadcrumbs = [
            [
                'label' => __('Payments'),
                'route' => route('payments.index'),
            ],
            [
                'label' => __('Payment imports'),
                'route' => route('payments.imports.index'),
            ],
            [
                'label' => $import->file_name,
                'route' => route('payments.imports.show', $import),
            ],
            [
                'label' => __('Map import'),
                'route' => route('payments.imports.map', $import),
            ],
        ];

        return inertia('payments/imports/Map', [
            'title' => $title,
            'breadcrumbs' => $breadcrumbs,
            'paymentImport' => $import->toResource(),
            'headers' => $import->headers,
        ])->withViewData(compact('title'));
    }
}
