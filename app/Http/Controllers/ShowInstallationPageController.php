<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;

class ShowInstallationPageController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function __invoke(Request $request)
    {
        $tenant = Tenant::firstOrNew(['domain' => $request->getHost()]);
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
