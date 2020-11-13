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

interface Relation extends Field
{

    /**
     * Is this a to-one relation?
     *
     * @return bool
     */
    public function toOne(): bool;

    /**
     * Is this a to-many relation?
     *
     * @return bool
     */
    public function toMany(): bool;

    /**
     * Get the inverse resource type.
     *
     * If the relation is polymorphic, it MUST implement
     * the `PolymorphicRelation` interface and return a
     * psuedo-type from this method.
     *
     * For example, if an `images` resource has an `imageable`
     * relation to which either a `posts` or `users` resource
     * could be related. The `inverse()` method would return
     * `imageables` and the `inverseTypes()` method would
     * return: `['posts', 'users']`.
     *
     * @return string
     */
    public function inverse(): string;

    /**
     * Is the relation allowed as an include path?
     *
     * @return bool
     */
    public function isIncludePath(): bool;

    /**
     * Get additional filters for the relation.
     *
     * Filters returned by this method are additional to the filters
     * that exist on the inverse resource type.
     *
     * @return Filter[]|iterable
     */
    public function filters(): iterable;
}
