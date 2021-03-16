<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Testing Routes
|--------------------------------------------------------------------------
|
| These routes are for Cypress tests to accommodate the needs of e2e testing
|
*/

Route::prefix('_testing')->group(function () {
    $email = 'user@example.com';

    /**
     * Creates a user session for the given tenant
     */
    Route::get('/session/new', function (Request $request) use ($email) {
        $tenant = \App\Models\Tenant::firstOrCreate(
            ['domain' => $request->getHost()],
            [
                'name' => 'Organization',
                'ps_url' => env('POWERSCHOOL_ADDRESS'),
                'ps_client_id' => env('POWERSCHOOL_CLIENT_ID'),
                'ps_secret' => env('POWERSCHOOL_CLIENT_SECRET'),
                'license' => \Ramsey\Uuid\Uuid::uuid4(),
                'allow_password_auth' => true,
                'subscription_started_at' => now(),
            ]
        );

        \App\Models\User::where('email', $email)->delete();

        /** @var \App\Models\User $user */
        $user = $tenant->users()->save(\App\Models\User::factory()->make(['email' => $email]));

        auth()->login($user);

        return response()->json();
    });

    /**
     * Logs the user out and deletes any user with that email
     */
    Route::get('/session/logout', function (\Illuminate\Http\Request $request) use ($email) {
        $user = $request->user();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($user) {
            $user->delete();
        }

        \App\Models\User::where('email', $email)->delete();

        return response()->json();
    });
});
