<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use LaravelJsonApi\Laravel\Facades\JsonApiRoute;

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
        $server->resource('users')->only('show')->relationships(function ($relationships) {
            $relationships->hasOne('phone');
        })->actions(function ($actions) {
            $actions->get('me');
        });

        /** Videos */
        $server->resource('videos')->relationships(function ($relationships) {
            $relationships->hasMany('tags');
        });
    });
