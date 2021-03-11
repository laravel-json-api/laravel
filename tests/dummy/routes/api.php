<?php

use LaravelJsonApi\Laravel\Facades\JsonApiRoute;

JsonApiRoute::server('v1')->prefix('v1')->namespace('Api\V1')->resources(function ($server) {
    /** Posts */
    $server->resource('posts')->relationships(function ($relationships) {
        $relationships->hasOne('author')->readOnly();
        $relationships->hasMany('comments')->readOnly();
        $relationships->hasMany('media')->readOnly();
        $relationships->hasMany('tags');
    })->actions('-actions', function ($actions) {
        $actions->delete('purge');
        $actions->withId()->post('publish');
    });

    /** Videos */
    $server->resource('videos')->relationships(function ($relationships) {
        $relationships->hasMany('tags');
    });
});
