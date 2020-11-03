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

namespace LaravelJsonApi\Core\Resolver;

use LaravelJsonApi\Core\Support\Str;
use function class_basename;

final class ResourceType
{

    /**
     * @var array
     */
    private static array $cache = [];

    /**
     * Manually register the resource type to use for a schema class.
     *
     * @param string $schemaClass
     * @param string $resourceType
     * @return void
     */
    public static function register(string $schemaClass, string $resourceType): void
    {
        self::$cache[$schemaClass] = $resourceType;
    }

    /**
     * Resolve the JSON API resource type from the fully-qualified schema class.
     *
     * @param string $schemaClass
     * @return string
     */
    public function __invoke(string $schemaClass): string
    {
        if (isset(self::$cache[$schemaClass])) {
            return self::$cache[$schemaClass];
        }

        return self::$cache[$schemaClass] = Str::plural(Str::dasherize(
            Str::replaceLast('Schema', '', class_basename($schemaClass))
        ));
    }
}
