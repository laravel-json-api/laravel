<?php

use App\Http\Controllers\Api\V1\PostController;
use Illuminate\Support\Facades\Route;
use LaravelJsonApi\Laravel\Facades\JsonApiRoute;

JsonApiRoute::server('v1')->prefix('v1')->namespace('Api\V1')->resources(function ($server) {
    $server->resource('posts')->relationships(function ($relationships) {
        $relationships->hasOne('author')->readOnly();
        $relationships->hasMany('comments')->readOnly();
        $relationships->hasMany('tags');
    });

    Route::post('posts/{post}/-actions/publish', [PostController::class, 'publish']);

    $server->resource('videos')->relationships(function ($relationships) {
        $relationships->hasMany('tags');
    });
});
