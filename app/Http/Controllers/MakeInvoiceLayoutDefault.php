<?php

namespace App\Http\Controllers;

use App\Models\InvoiceLayout;
use Illuminate\Http\Request;

class MakeInvoiceLayoutDefault extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, InvoiceLayout $layout)
    {
        $this->authorize('update', $layout);

        $request->school()
            ->invoiceLayouts()
            ->update(['is_default' => false]);
        $layout->update(['is_default' => true]);

        session()->flash('success', __('Layout changed to default layout.'));

        return back();
    }
}
