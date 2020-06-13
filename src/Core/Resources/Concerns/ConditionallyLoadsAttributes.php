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

namespace LaravelJsonApi\Core\Resources\Concerns;

use LaravelJsonApi\Core\Resources\ConditionalAttr;
use LaravelJsonApi\Core\Resources\ConditionalAttrs;

trait ConditionallyLoadsAttributes
{

    /**
     * Conditionally include an attribute value.
     *
     * @param bool $check
     * @param mixed $value
     * @return ConditionalAttr
     */
    protected function when(bool $check, $value): ConditionalAttr
    {
        return new ConditionalAttr($check, $value);
    }

    /**
     * Conditionally include a set of attribute values.
     *
     * @param bool $check
     * @param iterable $values
     * @return ConditionalAttrs
     */
    protected function mergeWhen(bool $check, iterable $values): ConditionalAttrs
    {
        return new ConditionalAttrs($check, $values);
    }
}
