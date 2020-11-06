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

namespace LaravelJsonApi\Eloquent;

use Illuminate\Database\Eloquent\Model;
use LaravelJsonApi\Core\Contracts\Schema\Container;
use LaravelJsonApi\Core\Contracts\Store\Repository as RepositoryContract;
use LaravelJsonApi\Core\Resolver\ResourceClass;
use LaravelJsonApi\Core\Resolver\ResourceType;
use LaravelJsonApi\Core\Schema\Schema as BaseSchema;
use LogicException;

abstract class Schema extends BaseSchema
{

    /**
     * @var callable|null
     */
    protected static $resourceTypeResolver;

    /**
     * @var callable|null
     */
    protected static $resourceResolver;

    /**
     * The key name for the resource id, or null to use the model's route key.
     *
     * @var string|null
     */
    protected ?string $primaryKey = null;

    /**
     * The relationships that should always be eager loaded.
     *
     * @var array
     */
    protected array $with = [];

    /**
     * @var Container|null
     */
    private ?Container $container;

    /**
     * @var array|null
     */
    private ?array $fields = null;

    /**
     * Specify the callback to use to guess the resource type from the schema class.
     *
     * @param callable $resolver
     * @return void
     */
    public static function guessTypeUsing(callable $resolver): void
    {
        static::$resourceTypeResolver = $resolver;
    }

    /**
     * @inheritDoc
     */
    public static function type(): string
    {
        $resolver = static::$resourceResolver ?: new ResourceType();

        return $resolver(static::class);
    }

    /**
     * @inheritDoc
     */
    public static function model(): string
    {
        if (isset(static::$model)) {
            return static::$model;
        }

        throw new LogicException('The model class name must be set.');
    }

    /**
     * Specify the callback to use to guess the resource class from the schema class.
     *
     * @param callable $resolver
     * @return void
     */
    public static function guessResourceUsing(callable $resolver): void
    {
        static::$resourceResolver = $resolver;
    }

    /**
     * @inheritDoc
     */
    public static function resource(): string
    {
        $resolver = static::$resourceResolver ?: new ResourceClass();

        return $resolver(static::class);
    }

    /**
     * @inheritDoc
     */
    public function repository(): RepositoryContract
    {
        return new Repository($this);
    }

    /**
     * @return Model
     */
    public function newInstance(): Model
    {
        $modelClass = $this->model();

        return new $modelClass;
    }

    /**
     * @return string
     */
    public function idName(): string
    {
        if ($this->primaryKey) {
            return $this->primaryKey;
        }

        return $this->primaryKey = $this->newInstance()->getRouteKeyName();
    }

    /**
     * @return EagerLoader
     */
    public function loader(): EagerLoader
    {
        return new EagerLoader($this->schemas(), $this);
    }

}
