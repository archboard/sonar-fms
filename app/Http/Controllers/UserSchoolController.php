<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;

class UserSchoolController extends Controller
{
    /**
     * Gets the list of schools and the user's access to them
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(User $user)
    {
        $this->authorize('manage tenancy');

        return response()
            ->json($user->getSchoolAccessList());
    }

    /**
     * Updates a user's school access
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('manage tenancy');

        $data = $request->validate([
            'schools' => 'required|array',
            'schools.*.id' => 'required|int',
            'schools.*.has_access' => 'required|boolean',
        ]);
        $ids = collect($data['schools'])
            ->filter(fn ($school) => $school['has_access'])
            ->pluck('id');

        // Fail validation if trying to save no schools
        // or the only schools are inactive
        if (
            $ids->isEmpty() ||
            School::whereIn('id', $ids)->inactive()->exists()
        ) {
            return response()->json([
                'level' => 'error',
                'message' => __('The user must have access to at least one active school.'),
            ], 422);
        }

        $user->schools()->sync($ids);

        return response()->json([
            'level' => 'success',
            'message' => __('School access updated successfully.'),
        ]);
    }
}
