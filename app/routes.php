<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function()
{
	return View::make('index');
});

Route::get('/login', [
	'as' => 'login_path',
	'uses' => 'SessionsController@login'
]);

Route::get('/logout', [
	'as' => 'logout_path',
	'uses' => 'SessionsController@logout'
]);
