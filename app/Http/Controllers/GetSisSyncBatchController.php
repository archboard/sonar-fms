<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;

class GetSisSyncBatchController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        if ($batchId = $request->tenant()->batch_id) {
            return response()->json(Bus::findBatch($batchId));
        }

        return response()->json((object) []);
    }
}
