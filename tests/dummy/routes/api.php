<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

use LaravelJsonApi\Laravel\Facades\JsonApiRoute;
use LaravelJsonApi\Laravel\Http\Controllers\JsonApiController;

JsonApiRoute::server('v1')
    ->prefix('v1')
    ->namespace('Api\V1')
    ->resources(function ($server) {
        /** Posts */
        $server->resource('posts')->relationships(function ($relationships) {
            $relationships->hasOne('author')->readOnly();
            $relationships->hasMany('comments')->readOnly();
            $relationships->hasMany('media');
            $relationships->hasMany('tags');
        })->actions('-actions', function ($actions) {
            $actions->delete('purge');
            $actions->withId()->post('publish');
        });

        /** Users */
        $server->resource('users')->only('show','destroy')->relationships(function ($relationships) {
            $relationships->hasOne('phone');
        })->actions(function ($actions) {
            $actions->get('me');
        });

        /** Videos */
        $server->resource('videos')->relationships(function ($relationships) {
            $relationships->hasMany('tags');
        });

        $server->resource('tags', '\\' . JsonApiController::class)->only('destroy');
    });
