<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class ChangeSchoolController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'school_id' => 'required|exists:schools,id',
        ]);

        /** @var User $user */
        $user = $request->user();

        if ($user->schools()->where($data)->doesntExist()) {
            session()->flash('error', __('You do not have access to this school.'));
            return back();
        }

        $user->update($data);
        session()->flash('success', __('School changed successfully.'));

        return back();
    }
}
