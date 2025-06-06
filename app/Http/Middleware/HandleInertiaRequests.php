<?php

namespace App\Http\Middleware;

use App\Models\Fee;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Scholarship;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'layouts.app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     *
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
     *
     * @return array
     */
    public function share(Request $request)
    {
        return array_merge(parent::share($request), [
            'user' => function () use ($request) {
                if ($user = $request->user()) {
                    $user->load('activeSchools');

                    return $user->toResource();
                }

                return (object) [];
            },
            'school' => function () {
                $school = app(School::class);
                $school->load('currency');

                return $school->toResource();
            },
            'locales' => config('app.locales'),
            'locale' => fn () => app()->getLocale(),
            'flash' => [
                'success' => session('success'),
                'error' => function () use ($request) {
                    if ($request->session()->has('errors')) {
                        return __('Please correct the invalid form fields and try again.');
                    }

                    return session('error');
                },
            ],
            'mainNav' => function () use ($request) {
                /** @var User|null $user */
                $user = $request->user();

                if (! $user) {
                    return [];
                }

                $links = [
                    [
                        'label' => __('Dashboard'),
                        'route' => route('home'),
                        'active' => $request->routeIs('home'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />',
                    ],
                ];

                if ($user->can('view', Student::class)) {
                    $links[] = [
                        'label' => __('Students'),
                        'route' => route('students.index'),
                        'active' => $request->routeIs('students.*'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>',
                    ];
                }

                if ($user->can('view', Invoice::class)) {
                    $links[] = [
                        'label' => __('Invoices'),
                        'route' => route('invoices.index'),
                        'active' => $request->routeIs('invoices.*') && ! $request->routeIs('invoices.imports.*'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />',
                    ];
                    $links[] = [
                        'label' => __('Invoice Imports'),
                        'route' => route('invoices.imports.index'),
                        'active' => $request->routeIs('invoices.imports*'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />',
                    ];
                }

                if ($user->can('view', InvoicePayment::class)) {
                    $links[] = [
                        'label' => __('Payments'),
                        'route' => route('payments.index'),
                        'active' => $request->routeIs('payments.*'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />',
                    ];
                }

                if ($user->can('view', Fee::class)) {
                    $links[] = [
                        'label' => __('Fees'),
                        'route' => route('fees.index'),
                        'active' => $request->routeIs('fees.*'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
                    ];
                }

                if ($user->can('view', Scholarship::class)) {
                    $links[] = [
                        'label' => __('Scholarships'),
                        'route' => route('scholarships.index'),
                        'active' => $request->routeIs('scholarships.*'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>',
                    ];
                }

                if ($user->can('view', User::class)) {
                    $links[] = [
                        'label' => __('Users'),
                        'route' => route('users.index'),
                        'active' => $request->routeIs('users.*'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />',
                    ];
                }

                if ($user->students()->count() > 0) {
                    $links[] = [
                        'label' => __('My students'),
                        'route' => route('my-students.index'),
                        'active' => $request->is('my-students*'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />',
                    ];
                    $links[] = [
                        'label' => __('My invoices'),
                        'route' => route('my-invoices.index'),
                        'active' => $request->is('my-invoices*'),
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />',
                    ];
                }

                return $links;
            },
            'subNav' => function () use ($request) {
                /** @var User|null $user */
                $user = $request->user();

                if (! $user) {
                    return [];
                }

                $links = [
                    [
                        'label' => __('Personal settings'),
                        'route' => route('settings.personal'),
                        'active' => $request->routeIs('settings.personal'),
                    ],
                ];

                if ($user->can('edit school settings')) {
                    $links[] = [
                        'label' => __('School settings'),
                        'route' => route('settings.school'),
                        'active' => $request->routeIs('settings.school') || $request->is('layouts*'),
                    ];
                }

                if ($user->manages_tenancy) {
                    $links[] = [
                        'label' => __('Tenant settings'),
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
            'breadcrumbs' => [],
        ]);
    }
}
