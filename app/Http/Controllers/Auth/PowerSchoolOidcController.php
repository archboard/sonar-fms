<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use GrantHolle\PowerSchool\Auth\Traits\AuthenticatesUsingPowerSchoolWithOidc;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class PowerSchoolOidcController extends Controller
{
    use AuthenticatesUsingPowerSchoolWithOidc;

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

    protected function authenticated(Request $request, $user, Collection $data)
    {
    }
}
