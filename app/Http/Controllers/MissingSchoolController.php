<?php

namespace App\Http\Controllers;

use App\Http\Resources\SchoolResource;
use App\Models\Tenant;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MissingSchoolController extends Controller
{
    public function index(Request $request, Tenant $tenant)
    {
        $schools = $tenant->schools()
            ->active()
            ->orderBy('name')
            ->get();
        $title = __('Select your school');

        return inertia('NoSchools', [
            'title' => $title,
            'schools' => SchoolResource::collection($schools),
        ])->withViewData(compact('title'));
    }

    public function update(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $data = $request->validate([
            'school_id' => [
                'required',
                Rule::exists('schools', 'id')
                    ->where('tenant_id', $user->tenant_id)
                    ->where('active', true),
            ],
        ]);

        $user->update($data);
        $user->schools()
            ->syncWithoutDetaching([$data['school_id']]);
        session()->flash('success', __('School set successfully.'));

        return redirect(RouteServiceProvider::HOME);
    }
}
