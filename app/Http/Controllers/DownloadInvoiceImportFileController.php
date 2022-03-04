<?php

namespace App\Http\Controllers;

use App\Models\InvoiceImport;
use Illuminate\Http\Request;

class DownloadInvoiceImportFileController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function __invoke(Request $request, InvoiceImport $import)
    {
        $this->authorize('view', $import);

        return $import->download();
    }
}
