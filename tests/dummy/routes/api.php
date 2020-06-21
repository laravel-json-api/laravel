<?php

use Illuminate\Support\Facades\Route;

Route::middleware('json-api:v1')->prefix('v1')->namespace('Api\V1')->group(function () {
    Route::get('posts', 'PostController@index');
    Route::get('posts/{post}', 'PostController@read');
});
