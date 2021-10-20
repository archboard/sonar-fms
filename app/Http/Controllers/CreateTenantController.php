<?php

namespace App\Http\Controllers;

use App\Jobs\SyncSchool;
use App\Jobs\SyncSchools;
use App\Models\School;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class CreateTenantController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        $tenant = Tenant::firstOrNew($request->only('license'));

        // Call home to validate the license?
        $data = $request->validate([
            'license' => 'required|uuid',
            'name' => 'required',
            'domain' => [
                'required',
                Rule::unique('tenants')->ignoreModel($tenant),
            ],
            'ps_url' => 'required|url',
            'ps_client_id' => 'required|uuid',
            'ps_secret' => 'required|uuid',
            'email' => 'required|email',
        ]);

        // Save the tenant
        $data['sync_notification_emails'] = $data['email'];
        $tenant->forceFill(Arr::except($data, 'email'));
        $tenant->save();
        $tenant->makeCurrent();

        // Save the user and give them full privileges
        /** @var User $user */
        $user = $tenant->users()->updateOrCreate(Arr::only($data, 'email'), [
            'manages_tenancy' => true,
        ]);

        // Kick off job to sync schools
        SyncSchools::dispatchSync($tenant);

        auth()->login($user);
        session()->flash('success', __('Information saved successfully. Update tenant settings to finish installation.'));

        return redirect()->route('settings.tenant');
    }
}
