<?php
/*
 * Copyright 2021 Cloud Creativity Limited
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

    /**
     * @var string
     */
    private string $type;

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

        try {
            $fqn = Str::replaceLast('Schema', $this->type, get_class(
                $app->make(SchemaContainer::class)->schemaFor($resourceType)
            ));

            if (!class_exists($fqn) && !$app->bound($fqn)) {
                if (true === $allowNull) {
                    return null;
                } else if ('CollectionQuery' === $this->type) {
                    $fqn = AnonymousCollectionQuery::class;
                } else if ('Query' === $this->type) {
                    $fqn = AnonymousQuery::class;
                }
            }

            return $app->make($fqn);
        } catch (BindingResolutionException $ex) {
           throw new LogicException(sprintf(
               'Unable to create request class of type [%s] for resource type %s.',
               $this->type,
               $resourceType
           ), 0, $ex);
        }
    }
}
