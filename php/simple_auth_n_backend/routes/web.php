<?php

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

Route::get('/', function () {
    return view('welcome');
});


Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
});

Route::get('/login', function () {
    return view('login/login');
});

Route::get('/dashboard', function () {
    return view('dashboard/dashboard');
});
Auth::routes();


Route::get('/user', 'UserTestController@index');
Route::get('/home', 'UserTestController@home')->name('user_home');
Route::get('/user/dashboard', 'UserTestController@dashboard')->name('user_dashboard');
Route::get('/user/profile', 'UserTestController@profile')->name('user_profile');


