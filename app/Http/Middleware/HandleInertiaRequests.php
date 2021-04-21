<?php

namespace App\Http\Middleware;

use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'layouts.app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request)
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request)
    {
        return array_merge(parent::share($request), [
            'user' => function () use ($request) {
                if ($user = $request->user()) {
                    $user->load('schools');

                    return $user->toResource();
                }

                return (object) [];
            },
            'school' => function () {
                return app(School::class)->toResource();
            },
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
            'mainNav' => function () use ($request) {
                /** @var User $user */
                $user = $request->user();

                if (!$user) {
                    return [];
                }

                $links = [
                    [
                        'label' => __('Dashboard'),
                        'route' => route('home'),
                        'active' => $request->routeIs('home'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />',
                    ]
                ];

                $links[] = [
                    'label' => __('Students'),
                    'route' => route('students.index'),
                    'active' => $request->routeIs('students.*'),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>',
                ];

                $links[] = [
                    'label' => __('Fees'),
                    'route' => route('home'),
                    'active' => $request->routeIs('fees.*'),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>',
                ];

                $links[] = [
                    'label' => __('Scholarships'),
                    'route' => route('home'),
                    'active' => $request->routeIs('scholarships.*'),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>',
                ];

                $links[] = [
                    'label' => __('Customers'),
                    'route' => route('home'),
                    'active' => $request->routeIs('customers.*'),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>',
                ];

                return $links;
            },
            'subNav' => function () use ($request) {
                /** @var User $user */
                $user = $request->user();

                if (!$user) {
                    return [];
                }

                $links = [
                    [
                        'label' => __('Personal settings'),
                        'route' => route('settings.personal'),
                        'active' => $request->routeIs('settings.personal'),
                    ]
                ];

                if ($user->can('edit school settings')) {
                    $links[] = [
                        'label' => __('School settings'),
                        'route' => route('settings.school'),
                        'active' => $request->routeIs('settings.school'),
                    ];
                }

                if ($user->can('edit tenant settings')) {
                    $links[] = [
                        'label' => __('System settings'),
                        'route' => route('settings.tenant'),
                        'active' => $request->routeIs('settings.tenant'),
                    ];
                }

                $links[] = [
                    'label' => __('Sign out'),
                    'route' => route('logout'),
                    'active' => false,
                ];

                return $links;
            },
        ]);
    }
}
