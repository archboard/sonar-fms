<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class PublishSelectedInvoicesController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $user->selectedInvoices()
            ->whereNull('published_at')
            ->update(['published_at' => now()]);

        session()->flash('success', __('Invoices published successfully.'));

        return back();
    }
}
