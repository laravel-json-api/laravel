<?php

use LaravelJsonApi\Laravel\Facades\JsonApiRoute;

JsonApiRoute::server('v1')->prefix('v1')->namespace('Api\V1')->resources(function ($server) {
    $server->resource('posts')->relationships(function ($relationships) {
        $relationships->hasOne('author')->readOnly();
        $relationships->hasMany('comments')->readOnly();
        $relationships->hasMany('tags');
    })->actions('-actions', function ($actions) {
        $actions->withId()->post('publish');
    });

    $server->resource('videos')->relationships(function ($relationships) {
        $relationships->hasMany('tags');
    });
});
