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

declare(strict_types=1);

namespace LaravelJsonApi\Contracts\Schema;

interface Container
{

    /**
     * Get a schema by JSON API resource type.
     *
     * @param string $resourceType
     * @return Schema
     */
    public function schemaFor(string $resourceType): Schema;

    /**
     * Get a list of all the supported resource types.
     *
     * @return array
     */
    public function types(): array;

    /**
     * Get a list of model classes mapped to their resource classes.
     *
     * @return array
     */
    public function resources(): array;
}
