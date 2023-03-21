<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::group([

    'middleware' => 'api',
    'prefix' => 'api/auth',
    'namespace' => 'Api'

], function () {

    Route::post('login', 'ApiAuthController@login');
    Route::post('logout', 'ApiAuthController@logout');
    Route::post('refresh', 'ApiAuthController@refresh');
    Route::get('me', 'ApiAuthController@me');
    Route::post('reset-password', 'ApiAuthController@resetPassword');
});