<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Resources\CurrencyResource;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        $currencies = Currency::orderBy('currency')
            ->get();

        return inertia('settings/School', [
            'title' => $title,
            'currencies' => CurrencyResource::collection($currencies),
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
            'currency_id' => 'required|exists:currencies,id',
            'timezone' => [
                'required',
                Rule::in(timezones()->keys())
            ],
            'collect_tax' => 'required|boolean',
            'include_draft_stamp' => 'required|boolean',
            'tax_rate' => 'required_if:collect_tax,true|numeric|min:0',
            'tax_label' => 'required_if:collect_tax,true',
            'invoice_number_template' => 'nullable',
            'default_title' => 'nullable',
        ]);

        $request->school()
            ->update($data);

        session()->flash('success', __('School settings updated successfully.'));

        return back();
    }
}
