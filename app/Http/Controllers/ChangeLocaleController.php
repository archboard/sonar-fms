<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChangeLocaleController extends Controller
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
            'locale' => [
                'required',
                // Rule::in(['']),
            ]
        ]);

        $request->user()
            ->update($data);

        app()->setLocale($data['locale']);

        session()->flash('success', __('Locale changed successfully.'));

        return back();
    }
}
