<?php
/**
 * Copyright 2020 Cloud Creativity Limited
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

namespace LaravelJsonApi\Contracts\Routing;

use Illuminate\Database\Eloquent\Model;
use LaravelJsonApi\Contracts\Schema\Schema;

interface Route
{

    /**
     * Get the resource type.
     *
     * @return string
     */
    public function resourceType(): string;

    /**
     * Get the resource id or the model (if bindings have been substituted).
     *
     * @return Model|mixed|string|null
     */
    public function modelOrResourceId();

    /**
     * Get the resource id.
     *
     * @return string
     */
    public function resourceId(): string;

    /**
     * Get the schema for the current route.
     *
     * @return Schema
     */
    public function schema(): Schema;
}