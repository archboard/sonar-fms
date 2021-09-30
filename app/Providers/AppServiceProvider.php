<?php

namespace App\Providers;

use App\Models\Invoice;
use App\Models\School;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        JsonResource::withoutWrapping();
        Inertia::setRootView('layouts.app');
        // \Bouncer::cache();

        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }

        $currentTenant = function (): Tenant {
            /** @var Tenant $current */
            $current = Tenant::current();

            return $current ?? new Tenant();
        };

        $currentSchool = function (): School {
            $current = School::current();

            return $current ?? new School();
        };

        if (!$this->app->runningInConsole()) {
            $this->app->bind(Tenant::class, $currentTenant);
            $this->app->bind(School::class, $currentSchool);
        }

        Request::macro('tenant', $currentTenant);
        Request::macro('school', $currentSchool);
        Request::macro('wantsInertia', function () {
            return request()->header('x-inertia');
        });

        Relation::enforceMorphMap([
            'user' => User::class,
            'student' => Student::class,
            'invoice' => Invoice::class,
        ]);
    }
}
