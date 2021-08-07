<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/v1/CreateEvent', 'BackendController@createEvent')->middleware('authenticate_call');
Route::get('/v1/GetCountOfEvents', 'BackendController@debugGetEventCount')->middleware('authenticate_call');
Route::get('/v1/GetRandomEvent', 'BackendController@getRandomEvent')->middleware('authenticate_call');
Route::get('/v1/GetEvents', 'BackendController@getEvents')->middleware('authenticate_call');

Route::get('/v1/ip/{ip}/map', 'BackendController@getImageFromIP')->middleware('authenticate_call');
Route::get('/v1/ip/{ip}/geoip', 'BackendController@getGeoIP')->middleware('authenticate_call');


Route::get('/v1/report/{id}', 'BackendController@getReportByID')->middleware('authenticate_call');
Route::get('/v1/report/{id}/map', 'BackendController@getImageByID')->middleware('authenticate_call');

Route::post('/v1/token/generate', 'BackendController@generateToken')->middleware('authenticate_call');


Route::fallback(function(){
    return response()->json([
        'Status' => '404',
        'Message' => 'The requested resource was not found on this server.'
    ], 404);
});
