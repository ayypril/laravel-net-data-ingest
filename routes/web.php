<?php

use Illuminate\Support\Facades\Route;


Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
Route::get('/login/discord', 'Auth\LoginController@redirectToDiscordOauth')->name('discordLogin');
Route::get('/login/discord/callback', 'Auth\LoginController@processDiscordCallback')->name('handlediscordCallback');
Route::post('/logout', 'Auth\LoginController@logout')->name('logout');


Route::get('/home', 'HomeController@index')->name('home');

Route::get('/info', 'HomeController@info')->name('info');

Route::group(['prefix' => 'account'], function() {
    Route::get('/', 'UserController@index');
    Route::delete('/', 'UserController@deleteAccount');
    Route::get('/keys', 'UserController@loadKeysPage')->middleware('verified');
    Route::post('/keys/regenerate', 'UserController@regenerateKeysPage')->middleware('verified');
});


// admin routes
Route::group(['prefix' => 'admin', 'middleware' => ['CheckIfAdmin']], function() {
    Route::get('/', 'AdminController@index');
    Route::get('/users', 'AdminController@getUserList');
    Route::put('/users/{user}/suspend', 'AdminController@suspendUser');
    Route::delete('/users/{user}/suspend', 'AdminController@unsuspendUser');
});
