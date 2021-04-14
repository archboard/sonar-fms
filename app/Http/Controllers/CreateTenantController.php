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
     * @param Tenant $tenant
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
        $tenant->forceFill(Arr::except($data, 'email'));
        $tenant->save();

        // Kick off job to sync schools
        SyncSchools::dispatchSync($tenant);

        // Save the user and give them full privileges
        /** @var User $user */
        $user = $tenant->users()->updateOrCreate(Arr::only($data, 'email'));
        $user->schools()->sync($tenant->schools->pluck('id'));

        // This is the equivalent of doing `everything()`
        $user->schools->each(function (School $school) use ($user) {
            $user->givePermissionForSchool($school);

            // Dispatch job to sync school in background
            SyncSchool::dispatch($school);
        });

        auth()->login($user);

        session()->flash('success', __('Installation complete. Sync has been started and will take several minutes to complete.'));

        return redirect()->route('home');
    }
}
