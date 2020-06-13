<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->namespace('Api\V1')->group(function () {
    Route::get('posts/{post}', 'PostController@read');
});
