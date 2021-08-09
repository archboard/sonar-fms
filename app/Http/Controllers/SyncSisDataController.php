<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;

class SyncSisDataController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        $tenant = $request->tenant();

        if ($tenant->batch_id) {
            $batch = Bus::findBatch($tenant->batch_id);

            if ($batch && !$batch->finished()) {
                session()->flash('error', __('SIS data is currently syncing.'));
                return back();
            }

            if (!$batch) {
                $tenant->update(['batch_id' => null]);
            }
        }

        $tenant->startSisSync();
        session()->flash('success', __('SIS data sync started.'));

        return back();
    }
}
