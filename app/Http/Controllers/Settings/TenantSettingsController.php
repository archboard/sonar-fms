<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Resources\SchoolResource;
use App\Http\Resources\SyncTimeResource;
use App\Models\School;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TenantSettingsController extends Controller
{
    /**
     * Shows the tenant settings form
     *
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function index(Request $request)
    {
        $title = __('Tenant Settings');
        $tenant = $request->tenant();
        $schools = School::orderBy('name')
            ->get();

        return inertia('settings/Tenant', [
            'title' => $title,
            'tenant' => $tenant->toArray(),
            'syncTimes' => SyncTimeResource::collection($tenant->syncTimes),
            'schools' => SchoolResource::collection($schools),
        ])->withViewData(compact('title'));
    }

    /**
     * Updates attributes for the tenant
     *
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
            'smtp_host' => [Rule::requiredIf(! config('app.cloud'))],
            'smtp_port' => [Rule::requiredIf(! config('app.cloud'))],
            'smtp_username' => ['nullable'],
            'smtp_password' => ['nullable'],
            'smtp_from_name' => [Rule::requiredIf(! config('app.cloud'))],
            'smtp_from_address' => [Rule::requiredIf(! config('app.cloud')), 'email'],
            'smtp_encryption' => ['nullable'],
        ]);

        Tenant::current()->update($data);

        session()->flash('success', __('Settings updated successfully.'));

        return back();
    }
}
