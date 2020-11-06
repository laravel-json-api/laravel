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

namespace LaravelJsonApi\Core\Contracts\Http;

use LaravelJsonApi\Core\Contracts\Resources\Container as ResourceContainer;
use LaravelJsonApi\Core\Contracts\Schema\Container as SchemaContainer;
use LaravelJsonApi\Core\Encoder\Encoder;
use LaravelJsonApi\Core\Store\Store;

interface Server
{
    /**
     * Bootstrap the server when it is handling an HTTP request.
     *
     * @return void
     */
    public function serving(): void;

    /**
     * Get the server's name.
     *
     * @return string
     */
    public function name(): string;

    /**
     * Get the server's schemas.
     *
     * @return SchemaContainer
     */
    public function schemas(): SchemaContainer;

    /**
     * Get the server's resources.
     *
     * @return ResourceContainer
     */
    public function resources(): ResourceContainer;

    /**
     * Get the server's store.
     *
     * @return Store
     */
    public function store(): Store;

    /**
     * Get the server's encoder.
     *
     * @return Encoder
     */
    public function encoder(): Encoder;
}
