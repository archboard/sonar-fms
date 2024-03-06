<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SaveSyncEmailsController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'sync_notification_emails' => 'required',
        ]);

        $request->tenant()->update($data);

        session()->flash('success', __('Email addresses saved successfully.'));

        return back();
    }
}
