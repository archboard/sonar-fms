<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UpdateUserPermissions extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function __invoke(Request $request, User $user)
    {
        $this->authorize('edit permissions', $user);

        $data = $request->validate([
            'models' => 'required|array',
            'models.*.model' => 'required',
            'models.*.permissions' => 'required|array',
            'models.*.permissions.*.permission' => 'required',
            'models.*.permissions.*.can' => 'required|boolean',
        ]);

        foreach ($data['models'] as $set) {
            foreach ($set['permissions'] as $permission) {
                $verb = $permission['can'] ? 'allow' : 'disallow';
                $user->$verb($permission['permission'], $set['model']);
            }
        }

        \Bouncer::refreshFor($user);

        return response()->json([
            'level' => 'success',
            'message' => __('Permissions updated successfully.'),
        ]);
    }
}
