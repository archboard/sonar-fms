<?php

namespace App\Http\Controllers;

use App\Http\Resources\SyncTimeResource;
use App\Models\SyncTime;
use Illuminate\Http\Request;

class SyncTimeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $syncTimes = SyncTime::orderBy('hour')->get();

        return SyncTimeResource::collection($syncTimes);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'hour' => 'required|integer',
        ]);

        // Just create or update for the same time
        $request->tenant()
            ->syncTimes()
            ->updateOrCreate($data);

        session()->flash('success', __('SIS sync time added successfully.'));

        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(SyncTime $syncTime)
    {
        $syncTime->delete();

        session()->flash('success', __('SIS sync time deleted successfully.'));

        return back();
    }
}
