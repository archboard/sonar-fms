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

    // PowerSchool OpenID 2.0 auth
    Route::get('/auth/powerschool/openid', [\App\Http\Controllers\Auth\PowerSchoolOpenIdLoginController::class, 'authenticate']);
    Route::get('/auth/powerschool/openid/verify', [\App\Http\Controllers\Auth\PowerSchoolOpenIdLoginController::class, 'login'])
        ->name('openid.verify');

    // PowerSchool OpenID Connect
    Route::get('/auth/powerschool/oidc/login', [\App\Http\Controllers\Auth\PowerSchoolOidcController::class, 'authenticate'])
        ->name('oidc.login');
    Route::middleware('allows_oidc_auth')
        ->get('/auth/powerschool/oidc', [\App\Http\Controllers\Auth\PowerSchoolOidcController::class, 'login']);

    // Normal auth
    require __DIR__.'/auth.php';

    Route::middleware('auth')->group(function () {
        Route::get('/ping', \App\Http\Controllers\CheckAuthStatusController::class)
            ->name('auth.status');

        Route::get('/csrf-token', \App\Http\Controllers\RefreshCsrfTokenController::class)
            ->name('csrf-token');

        Route::get('/home', \App\Http\Controllers\HomeController::class)
            ->name('home');

        Route::put('/change-schools', \App\Http\Controllers\ChangeSchoolController::class)
            ->name('schools.change');

        Route::get('/students', [\App\Http\Controllers\StudentController::class, 'index'])
            ->name('students.index');

        Route::get('/students/{student}', [\App\Http\Controllers\StudentController::class, 'show'])
            ->name('students.show');

        Route::post('/students/{student}/guardians/sync', \App\Http\Controllers\SyncStudentGuardiansController::class)
            ->name('students.guardians.sync');

        Route::resource('/student-selection', \App\Http\Controllers\StudentSelectionController::class)
            ->except('create', 'show', 'edit');

        Route::delete('/student-selection', \App\Http\Controllers\RemoveStudentSelectionController::class)
            ->name('student-selection.remove');

        Route::resource('/departments', \App\Http\Controllers\DepartmentController::class)
            ->except('create', 'edit');

        Route::resource('/fee-categories', \App\Http\Controllers\FeeCategoryController::class)
            ->except('create', 'edit');

        Route::resource('/users', \App\Http\Controllers\UserController::class)
            ->except('create');

        Route::prefix('/settings')->group(function () {
            Route::post('personal', [\App\Http\Controllers\Settings\PersonalSettingsController::class, 'update']);
            Route::get('personal', [\App\Http\Controllers\Settings\PersonalSettingsController::class, 'index'])
                ->name('settings.personal');

            Route::middleware('can:edit school settings')->group(function () {
                Route::post('school', [\App\Http\Controllers\Settings\SchoolSettingsController::class, 'update']);
                Route::get('school', [\App\Http\Controllers\Settings\SchoolSettingsController::class, 'index'])
                    ->name('settings.school');
            });

            Route::middleware('manages_tenancy')->group(function () {
                Route::post('tenant', [\App\Http\Controllers\Settings\TenantSettingsController::class, 'update']);
                Route::get('tenant', [\App\Http\Controllers\Settings\TenantSettingsController::class, 'index'])
                    ->name('settings.tenant');

                Route::resource('sync-times', \App\Http\Controllers\SyncTimeController::class)
                    ->only('index', 'store', 'destroy');

                Route::post('sync', \App\Http\Controllers\SyncSisDataController::class)
                    ->name('sis.sync');

                Route::put('sync/emails', \App\Http\Controllers\Settings\SaveSyncEmailsController::class)
                    ->name('sis.sync.emails');

                Route::get('sync/progress', \App\Http\Controllers\GetSisSyncBatchController::class)
                    ->name('sis.sync.batch');

                Route::put('tenant/schools', \App\Http\Controllers\Settings\SaveActiveSchoolsController::class)
                    ->name('tenant.schools');
            });
        });
    });
});
