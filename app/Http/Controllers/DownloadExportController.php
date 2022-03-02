<?php

namespace App\Http\Controllers;

use App\Models\RecordExport;
use Illuminate\Http\Request;

class DownloadExportController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Exports\RecordsExport
     */
    public function __invoke(Request $request, RecordExport $export)
    {
        if ($request->user()->uuid !== $export->user_uuid) {
            abort(404);
        }

        return $export->download();
    }
}
