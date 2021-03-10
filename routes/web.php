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

Route::get('/auth/powerschool/openid', [\App\Http\Controllers\Auth\PowerSchoolOpenIdLoginController::class, 'authenticate']);
Route::get('/auth/powerschool/openid/verify', [\App\Http\Controllers\Auth\PowerSchoolOpenIdLoginController::class, 'login'])
    ->name('openid.verify');

Route::middleware('tenant')->group(function () {
    Route::get('/', function () {
        return inertia('Index');
    });
});
