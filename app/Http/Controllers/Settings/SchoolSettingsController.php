<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SchoolSettingsController extends Controller
{
    /**
     * Show the school settings page
     *
     * @param Request $request
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function index(Request $request)
    {
        $title = __(':school_name Settings', ['school_name' => $request->school()->name]);

        return inertia('settings/School', [
            'title' => $title,
        ])->withViewData(compact('title'));
    }

    /**
     * Updates the current school
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'currency_symbol' => 'required',
            'currency_decimals' => 'required|integer',
        ]);

        $request->school()
            ->update($data);

        session()->flash('success', __('School settings updated successfully.'));

        return back();
    }
}
