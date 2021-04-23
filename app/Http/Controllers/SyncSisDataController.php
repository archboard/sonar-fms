<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
            session()->flash('error', __('SIS data is currently syncing.'));
            return back();
        }

        $tenant->startSisSync();
        session()->flash('success', __('SIS data sync started.'));

        return back();
    }
}
