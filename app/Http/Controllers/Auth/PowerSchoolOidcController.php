<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use GrantHolle\PowerSchool\Auth\Traits\AuthenticatesUsingPowerSchoolWithOidc;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PowerSchoolOidcController extends Controller
{
    use AuthenticatesUsingPowerSchoolWithOidc {
        authenticate as traitAuthenticate;
    }

    protected function getPowerSchoolUrl(): string
    {
        return Tenant::current()->ps_url;
    }

    protected function getClientId(): string
    {
        return Tenant::current()->ps_client_id;
    }

    public function getClientSecret(): string
    {
        return Tenant::current()->ps_secret;
    }

    public function authenticate(Request $request)
    {
        $tenant = $request->tenant();

        if ($tenant->allow_oidc_login) {
            return $this->traitAuthenticate($request);
        }

        return redirect($tenant->ps_url);
    }

    protected function authenticated(Request $request, User $user, Collection $data)
    {
        if ($data->get('persona') === 'staff') {
            $user->setSchoolStaffSchools()
                ->assign('staff');
        }

        if ($data->get('persona') === 'parent') {
            $user->setContactId()
                ->syncStudents()
                ->assign('contact');
        }

        $user->setSchool()
            ->save();
    }

    /**
     * Gets the default attributes to be added for this user
     */
    protected function getDefaultAttributes(Request $request, Collection $data): array
    {
        return [
            'tenant_id' => $request->tenant()->id,
        ];
    }
}
