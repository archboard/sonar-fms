<?php

namespace App\Tasks;

use Spatie\Multitenancy\Models\Tenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

class ScopeBouncer implements SwitchTenantTask
{
    public function makeCurrent(Tenant $tenant): void
    {
        // TODO: Implement makeCurrent() method.
    }

    public function forgetCurrent(): void
    {
        // TODO: Implement forgetCurrent() method.
    }
}
