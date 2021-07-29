<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;

class ShowInstallationPageController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param Tenant $tenant
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function __invoke(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $tenant = Tenant::firstOrNew(['domain' => $request->getHost()]);

        // Authorize if there is a user and don't have permission
        // This permission doesn't exist anywhere, so only someone
        // who manages the tenancy will pass this gate
        if ($user) {
            $this->authorize('install tenant');
        }

        // Or there isn't a user and the tenant has users
        if ($tenant->hasBeenInstalled()) {
            abort(404);
        }

        $title = __('Install Sonar FMS');

        return inertia('Install', [
            'title' => $title,
            'tenant' => [
                'name' => $tenant->name,
                'domain' => $tenant->domain,
                'ps_url' => $tenant->ps_url,
                'ps_client_id' => $tenant->ps_client_id,
                'ps_secret' => $tenant->ps_secret,
                'license' => $tenant->license,
            ],
            'email' => optional($request->user())->email,
        ])->withViewData(compact('title'));
    }
}
