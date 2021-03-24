<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


/**
 * Self-hosted only routes
 */
Route::middleware('self_hosted')->group(function () {
    Route::get('/install', \App\Http\Controllers\ShowInstallationPageController::class);
    Route::post('/install', \App\Http\Controllers\CreateTenantController::class)
        ->name('install');
});

Route::middleware('tenant')->group(function () {
    Route::get('/', \App\Http\Controllers\IndexController::class);

    // PowerSchool auth
    Route::get('/auth/powerschool/openid', [\App\Http\Controllers\Auth\PowerSchoolOpenIdLoginController::class, 'authenticate']);
    Route::get('/auth/powerschool/openid/verify', [\App\Http\Controllers\Auth\PowerSchoolOpenIdLoginController::class, 'login'])
        ->name('openid.verify');

    // Normal auth
    Route::middleware('allows_pw_auth')->group(function () {
        require __DIR__.'/auth.php';
    });

    Route::middleware('auth')->group(function () {
        Route::get('/ping', \App\Http\Controllers\CheckAuthStatusController::class)
            ->name('auth.status');

        Route::get('/logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])
            ->name('logout');

        Route::get('/csrf-token', \App\Http\Controllers\RefreshCsrfTokenController::class)
            ->name('csrf-token');

        Route::get('/home', \App\Http\Controllers\HomeController::class)
            ->name('home');

        Route::get('/students', [\App\Http\Controllers\StudentController::class, 'index'])
            ->name('students.index');

        Route::resource('/student-selection', \App\Http\Controllers\StudentSelectionController::class)
            ->except('create', 'show', 'edit');

        Route::delete('/student-selection', \App\Http\Controllers\RemoveStudentSelectionController::class)
            ->name('student-selection.remove');

        Route::prefix('/settings')->group(function () {
            Route::post('personal', [\App\Http\Controllers\Settings\PersonalSettingsController::class, 'update']);
            Route::get('personal', [\App\Http\Controllers\Settings\PersonalSettingsController::class, 'index'])
                ->name('settings.personal');

            Route::middleware('can:edit tenant settings')->group(function () {
                Route::post('tenant', [\App\Http\Controllers\Settings\TenantSettingsController::class, 'update']);
                Route::get('tenant', [\App\Http\Controllers\Settings\TenantSettingsController::class, 'index'])
                    ->name('settings.tenant');
            });
        });
    });
});
