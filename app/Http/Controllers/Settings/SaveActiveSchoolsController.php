<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;

class SaveActiveSchoolsController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'schools' => 'required|array',
        ]);

        School::whereNotIn('id', $data['schools'])
            ->update(['active' => false]);
        School::whereIn('id', $data['schools'])
            ->update(['active' => true]);

        session()->flash('success', __('Active schools updated successfully.'));

        return back();
    }
}
