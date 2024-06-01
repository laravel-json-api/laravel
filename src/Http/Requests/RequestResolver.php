<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
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
     * @param RequestMethod|null $requestMethod
     * @return FormRequest|null
     */
    public function __invoke(string $resourceType, bool $allowNull = false, RequestMethod $requestMethod = null): ?FormRequest
    {
        $app = app();

        $fqn = $this->custom($resourceType) ?: Str::replaceLast('Schema', $this->type, get_class(
            $app->make(SchemaContainer::class)->schemaFor($resourceType)
        ));

        if ($requestMethod) {
            $requestFqn = Str::replaceLast($resourceType, $requestMethod->value . $resourceType, $fqn);
            if (class_exists($requestFqn) || $app->bound($requestFqn)) {
                $fqn = $requestFqn;
            }
        }

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
