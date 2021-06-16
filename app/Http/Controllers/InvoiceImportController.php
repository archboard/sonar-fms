<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvoiceImportResource;
use App\Models\InvoiceImport;
use App\Models\School;
use Illuminate\Http\Request;

class InvoiceImportController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(InvoiceImport::class, 'import');
    }

    public function index(Request $request, School $school)
    {
        $title = __('Invoice Imports');
        $imports = $school->invoiceImports()
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('perPage', 15));

        return inertia('invoices/imports/Index', [
            'title' => $title,
            'imports' => InvoiceImportResource::collection($imports),
        ])->withViewData(compact('title'));
    }
}
