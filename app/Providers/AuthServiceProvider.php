<?php

namespace App\Providers;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\InvoiceRefund;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::before(fn (User $user, $ability) => $user->manages_tenancy ? true : null);

        Gate::define('manage tenancy', fn (User $user) => $user->manages_tenancy);

        Gate::define(
            'view invoice',
            fn (User $user, Invoice $invoice) => $user->canViewInvoice($invoice)
        );

        Gate::define(
            'view invoice payments',
            fn (User $user, Invoice $invoice) => $user->can('view', InvoicePayment::class) ||
                $user->hasImplicitAccessToInvoice($invoice)
        );

        Gate::define(
            'view invoice payment',
            fn (User $user, InvoicePayment $payment) => $user->can('view', InvoicePayment::class) ||
                $user->hasImplicitAccessToInvoice($payment->invoice)
        );

        Gate::define(
            'view invoice refunds',
            fn (User $user, Invoice $invoice) => $user->can('view', InvoiceRefund::class) ||
                $user->hasImplicitAccessToInvoice($invoice)
        );

        Gate::define(
            'view invoice refund',
            fn (User $user, InvoiceRefund $refund) => $user->can('view', $refund) ||
                $user->hasImplicitAccessToInvoice($refund->invoice)
        );
    }
}
