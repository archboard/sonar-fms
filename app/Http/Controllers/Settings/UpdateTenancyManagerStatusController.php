<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UpdateTenancyManagerStatusController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request, User $user)
    {
        $user->manages_tenancy = ! $user->manages_tenancy;
        $user->save();

        $message = $user->manages_tenancy
            ? __(':name can manage your tenancy.', ['name' => $user->full_name])
            : __(':name does not manage your tenancy now.', ['name' => $user->full_name]);

        \Bouncer::refreshFor($user);

        return response()->json([
            'level' => 'success',
            'message' => $message,
        ]);
    }
}
