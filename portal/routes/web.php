<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public website routes
|--------------------------------------------------------------------------
| Everything inside Route::localized() exists in English (/about) and Hindi
| (/hi/about). Hindi route names are prefixed with "hi." (hi.about).
*/

Route::localized(function () {
    Route::view('/', 'web.home')->name('home');
});
