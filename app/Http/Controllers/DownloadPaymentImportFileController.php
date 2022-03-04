<?php

namespace App\Http\Controllers;

use App\Models\PaymentImport;
use Illuminate\Http\Request;

class DownloadPaymentImportFileController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function __invoke(Request $request, PaymentImport $import)
    {
        $this->authorize('view', $import);

        return $import->download();
    }
}
