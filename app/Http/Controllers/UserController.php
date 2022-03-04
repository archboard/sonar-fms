<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function index(Request $request)
    {
        $school = $request->school();
        $title = __('Users');
        /** @var User $user */
        $user = $request->user();

        $users = $school->users()
            ->with('roles')
            ->filter($request->all())
            ->paginate($request->input('perPage', 25));

        return inertia('users/Index', [
            'users' => UserResource::collection($users),
            'title' => $title,
            'permissions' => $user->getPermissions(User::class),
            'isSchoolAdmin' => $user->isSchoolAdmin(),
        ])->withViewData(compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
        ]);
        $school = $request->school();

        $user = User::where('email', 'ilike', $data['email'])
            ->first();

        if (!$user) {
            $data['tenant_id'] = $school->tenant_id;
            $data['school_id'] = $school->id;
            $data['email'] = strtolower($data['email']);
            $user = User::create($data);
        }

        $user->schools()->syncWithoutDetaching([$school->id]);

        session()->flash('success', __('User created successfully.'));

        return redirect()->route('users.show', $user);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Inertia\Response|\Inertia\ResponseFactory
     */
    public function show(User $user)
    {
        $title = $user->full_name;
        $user->load('schools');

        return inertia('users/Show', [
            'title' => $title,
            'user' => $user->toResource(),
            'permissions' => $user->getPermissionsMatrix(),
        ])->withViewData(compact('title'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
