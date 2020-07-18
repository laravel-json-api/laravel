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

namespace LaravelJsonApi\Eloquent\Contracts;

use Illuminate\Http\Request;

interface Attribute
{

    /**
     * Get the JSON API field name.
     *
     * @return string
     */
    public function name(): string;

    /**
     * Get the model column for the field.
     *
     * @return string
     */
    public function column(): string;

    /**
     * Get the JSON type of the field.
     *
     * @return string
     */
    public function type(): string;

    /**
     * Can resources be sorted by the field?
     *
     * @return bool
     */
    public function isSortable(): bool;

    /**
     * Is the field read-only?
     *
     * @param Request $request
     * @return bool
     */
    public function isReadOnly($request): bool;

    /**
     * Can the field be listed in sparse field sets?
     *
     * @return bool
     */
    public function isSparseField(): bool;

}
