<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1', 'middleware' => 'throttle:1000,1'], function () {
    // Global
    Route::group(['prefix' => 'global'], function () {
        Route::group(['prefix' => 'user', 'controller' => 'UserController'], function () {
            Route::group(['middleware' => 'auth.global'], function () {
                Route::post('logout', 'logout');
                Route::get('check-token', 'checkToken');
            });
            Route::post('login', 'login');
        });
    });

    // Web
    Route::group(['prefix' => 'web', 'namespace'  => 'WEB'], function () {
        // Calendar Event
        Route::apiResource('calendar', 'CalendarEventController');
    });
});
