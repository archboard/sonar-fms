<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
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
    public function __invoke(Request $request, Tenant $tenant)
    {
        $title = __('Install');

        return inertia('Install', [
            'title' => $title,
            'tenant' => $tenant->toResource(),
        ])->withViewData(compact('title'));
    }
}
