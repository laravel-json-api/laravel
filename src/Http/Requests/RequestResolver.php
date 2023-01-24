<?php
/*
 * Copyright 2023 Cloud Creativity Limited
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

namespace LaravelJsonApi\Laravel\Http\Requests;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Http\FormRequest;
use LaravelJsonApi\Contracts\Schema\Container as SchemaContainer;
use LaravelJsonApi\Core\Support\Str;
use LogicException;
use function app;
use function sprintf;

class RequestResolver
{

    public const QUERY = 'Query';
    public const COLLECTION_QUERY = 'CollectionQuery';
    public const REQUEST = 'Request';

    /**
     * @var array
     */
    private static array $custom = [];

    /**
     * @var array
     */
    private static array $defaults = [
        self::QUERY => AnonymousQuery::class,
        self::COLLECTION_QUERY => AnonymousCollectionQuery::class,
    ];

    /**
     * @var string
     */
    private string $type;

    /**
     * Use the provided class as the default class for the specified request type.
     *
     * @param string $type
     * @param string $class
     */
    public static function useDefault(string $type, string $class): void
    {
        self::$defaults[$type] = $class;
    }

    /**
     * Register a custom binding.
     *
     * @param string $type
     * @param string $resourceType
     * @param string $class
     */
    public static function register(string $type, string $resourceType, string $class): void
    {
        self::$custom[$type] = self::$custom[$type] ?? [];
        self::$custom[$type][$resourceType] = $class;
    }

    /**
     * ResourceRequest constructor.
     *
     * @param string $type
     */
    public function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * @param string $resourceType
     * @param bool $allowNull whether null can be returned for non-existent classes.
     * @return FormRequest|null
     */
    public function __invoke(string $resourceType, bool $allowNull = false): ?FormRequest
    {
        $app = app();

        $fqn = $this->custom($resourceType) ?: Str::replaceLast('Schema', $this->type, get_class(
            $app->make(SchemaContainer::class)->schemaFor($resourceType)
        ));

        if (!class_exists($fqn) && !$app->bound($fqn)) {
            if (true === $allowNull) {
                return null;
            } else if (isset(self::$defaults[$this->type])) {
                $fqn = self::$defaults[$this->type];
            }
        }

        try {
            return $app->make($fqn);
        } catch (BindingResolutionException $ex) {
           throw new LogicException(sprintf(
               'Unable to create request class %s for resource type %s.',
               $fqn,
               $resourceType
           ), 0, $ex);
        }
    }

    /**
     * Check whether a custom class has been registered for the resource type.
     *
     * @param string $resourceType
     * @return string|null
     */
    private function custom(string $resourceType): ?string
    {
        $values = self::$custom[$this->type] ?? [];

        return $values[$resourceType] ?? null;
    }
}
