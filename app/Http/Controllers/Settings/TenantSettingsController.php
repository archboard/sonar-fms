<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantSettingsController extends Controller
{
    /**
     * Shows the tenant settings form
     *
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function index()
    {
        $title = __('Tenant Settings');

        return inertia('settings/Tenant', [
            'title' => $title,
            'tenant' => Tenant::current()->toArray(),
        ])->withViewData(compact('title'));
    }

    /**
     * Updates attributes for the tenant
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'license' => 'required|uuid',
            'name' => 'required',
            'ps_url' => 'required|url',
            'ps_client_id' => 'required|uuid',
            'ps_secret' => 'required|uuid',
            'allow_password_auth' => 'required|boolean',
            'allow_oidc_login' => 'required|boolean',
        ]);

        Tenant::current()->update($data);

        session()->flash('success', __('Settings updated successfully.'));

        return back();
    }
}
