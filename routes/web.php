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
    // PowerSchool auth
    Route::get('/auth/powerschool/openid', [\App\Http\Controllers\Auth\PowerSchoolOpenIdLoginController::class, 'authenticate']);
    Route::get('/auth/powerschool/openid/verify', [\App\Http\Controllers\Auth\PowerSchoolOpenIdLoginController::class, 'login'])
        ->name('openid.verify');

    Route::get('/', function () {
        return inertia('Index');
    });
});
