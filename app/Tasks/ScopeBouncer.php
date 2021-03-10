<?php

namespace App\Tasks;

use Silber\Bouncer\BouncerFacade;
use Spatie\Multitenancy\Models\Tenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

class ScopeBouncer implements SwitchTenantTask
{
    public function makeCurrent(Tenant $tenant): void
    {
        BouncerFacade::scope()->to($tenant->id);
    }

    public function forgetCurrent(): void
    {
        // TODO: Implement forgetCurrent() method.
    }
}
