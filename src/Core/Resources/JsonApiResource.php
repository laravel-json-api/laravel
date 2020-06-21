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
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\DelegatesToResource;
use LaravelJsonApi\Core\Document\Link;
use LaravelJsonApi\Core\Document\LinkHref;
use LaravelJsonApi\Core\Document\Links;
use LaravelJsonApi\Core\Resources\Concerns\ConditionallyLoadsAttributes;
use LogicException;
use function sprintf;

abstract class JsonApiResource implements ArrayAccess, Responsable
{

    use ConditionallyLoadsAttributes;
    use DelegatesToResource;

    /**
     * @var Model|mixed
     */
    public $resource;

    /**
     * JsonApiResource constructor.
     *
     * @param $resource
     */
    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    /**
     * @return string
     */
    abstract public function type(): string;

    /**
     * @return string
     */
    abstract public function selfUrl(): string;

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
    public function id(): string
    {
        return (string) $this->resource->getRouteKey();
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
        if ($relation = $this->relationships()[$name] ?? null) {
            return $relation;
        }

        throw new LogicException(sprintf(
            'Unexpected relationship %s on resource %s.',
            $name,
            $this->type()
        ));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        return (new ResourceResponse($this))->toResponse($request);
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
}
