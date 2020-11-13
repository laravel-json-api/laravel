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

namespace LaravelJsonApi\Core\Resources;

use ArrayAccess;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\DelegatesToResource;
use LaravelJsonApi\Core\Document\Link;
use LaravelJsonApi\Core\Document\LinkHref;
use LaravelJsonApi\Core\Document\Links;
use LaravelJsonApi\Core\Document\ResourceIdentifier;
use LaravelJsonApi\Core\Facades\JsonApi;
use LaravelJsonApi\Core\Resources\Concerns\ConditionallyLoadsAttributes;
use LaravelJsonApi\Core\Responses\ResourceResponse;
use LaravelJsonApi\Core\Support\Str;
use LogicException;
use function sprintf;

abstract class JsonApiResource implements ArrayAccess, Responsable
{

    use ConditionallyLoadsAttributes;
    use DelegatesToResource;

    /**
     * @var Model|object
     */
    public object $resource;

    /**
     * @var string
     */
    protected string $type = '';

    /**
     * @var array
     */
    private static array $types = [];

    /**
     * @var string|null
     */
    private ?string $selfUri = null;

    /**
     * JsonApiResource constructor.
     *
     * @param Model|object $resource
     */
    public function __construct(object $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @return iterable
     */
    abstract public function attributes(): iterable;

    /**
     * @return iterable
     */
    abstract public function relationships(): iterable;

    /**
     * @return string
     */
    public function selfUrl(): string
    {
        if ($this->selfUri) {
            return $this->selfUri;
        }

        return $this->selfUri = JsonApi::server()->url([
            $this->type(),
            $this->id(),
        ]);
    }

    /**
     * @return string
     */
    public function type(): string
    {
        if (!empty($this->type)) {
            return $this->type;
        }

        return $this->type = $this->guessType();
    }

    /**
     * @return string
     */
    public function id(): string
    {
        if ($this->resource instanceof UrlRoutable) {
            return $this->resource->getRouteKey();
        }

        throw new LogicException('Resource is not URL routable: you must implement the id method yourself.');
    }

    /**
     * @return bool
     */
    public function wasCreated(): bool
    {
        if ($this->resource instanceof Model) {
            return $this->resource->wasRecentlyCreated;
        }

        return false;
    }

    /**
     * @return ResourceIdentifier
     */
    public function identifier(): ResourceIdentifier
    {
        return new ResourceIdentifier(
            $this->type(),
            $this->id()
        );
    }

    /**
     * @return array
     */
    public function meta(): array
    {
        return [];
    }

    /**
     * @return bool
     */
    public function hasMeta(): bool
    {
        return !empty($this->meta());
    }

    /**
     * @return Links
     */
    public function links(): Links
    {
        return new Links($this->selfLink());
    }

    /**
     * @param string $name
     * @return Relation
     */
    public function relationship(string $name): Relation
    {
        /** @var Relation $relation */
        foreach ($this->relationships() as $relation) {
            if ($relation->fieldName() === $name) {
                return $relation;
            }
        }

        throw new LogicException(sprintf(
            'Unexpected relationship %s on resource %s.',
            $name,
            $this->type()
        ));
    }

    /**
     * @param Request $request
     * @return ResourceResponse
     */
    public function prepareResponse($request): ResourceResponse
    {
        return new ResourceResponse($this);
    }

    /**
     * @inheritDoc
     */
    public function toResponse($request)
    {
        return $this->prepareResponse($request)->toResponse($request);
    }

    /**
     * @return Link
     */
    protected function selfLink(): Link
    {
        return new Link('self', new LinkHref($this->selfUrl()));
    }

    /**
     * @param string $fieldName
     * @param string|null $keyName
     * @return Relation
     */
    protected function relation(string $fieldName, string $keyName = null): Relation
    {
        return new Relation($this, $fieldName, $keyName);
    }

    /**
     * @return string
     */
    protected static function guessType(): string
    {
        $fqn = static::class;

        if (isset(static::$types[$fqn])) {
            return static::$types[$fqn];
        }

        return static::$types[$fqn] = Str::dasherize(Str::plural(
            Str::before(class_basename($fqn), 'Resource')
        ));
    }
}
