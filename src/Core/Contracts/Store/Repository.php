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

namespace LaravelJsonApi\Core\Contracts\Store;

use Illuminate\Database\Eloquent\Model;

interface Repository
{

    /**
     * Get the model for the supplied resource id.
     *
     * @param string $resourceId
     * @return Model|mixed|null
     */
    public function find(string $resourceId);

    /**
     * Does a model with the supplied resource id exist?
     *
     * @param string $resourceId
     * @return bool
     */
    public function exists(string $resourceId): bool;
}
