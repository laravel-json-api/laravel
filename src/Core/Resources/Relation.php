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

use Closure;
use Illuminate\Database\Eloquent\Model;
use LaravelJsonApi\Core\Document\Link;
use LaravelJsonApi\Core\Document\Links;
use LaravelJsonApi\Core\Support\Str;
use LogicException;

class Relation
{

    /**
     * @var JsonApiResource
     */
    private $resource;

    /**
     * @var string
     */
    private $fieldName;

    /**
     * @var string|null
     */
    private $keyName;

    /**
     * @var mixed|null
     */
    private $data;

    /**
     * @var bool
     */
    private $hasData;

    /**
     * @var bool
     */
    private $showData;

    /**
     * @var bool
     */
    private $showSelf;

    /**
     * @var bool
     */
    private $showRelated;

    /**
     * @var array|Closure
     */
    private $meta;

    /**
     * Relation constructor.
     *
     * @param JsonApiResource $resource
     * @param string $fieldName
     * @param string|null $keyName
     */
    public function __construct(
        JsonApiResource $resource,
        string $fieldName,
        string $keyName = null
    ) {
        $this->resource = $resource;
        $this->fieldName = $fieldName;
        $this->keyName = $keyName ?: $fieldName;
        $this->hasData = false;
        $this->showData = false;
        $this->showSelf = true;
        $this->showRelated = true;
    }

    /**
     * @return string
     */
    public function fieldName(): string
    {
        return $this->fieldName;
    }

    /**
     * @return Links
     */
    public function links(): Links
    {
        $links = new Links();

        if ($this->showSelf) {
            $links->push(new Link('self', $this->selfUrl()));
        }

        if ($this->showRelated) {
            $links->push(new Link('related', $this->relatedUrl()));
        }

        return $links;
    }

    /**
     * @return array|null
     */
    public function meta(): ?array
    {
        if ($this->meta instanceof Closure) {
            $this->meta = ($this->meta)();
        }

        return $this->meta;
    }

    /**
     * @return mixed
     */
    public function data()
    {
        if (false === $this->hasData) {
            return $this->resource->{$this->keyName};
        }

        if ($this->data instanceof Closure) {
            return ($this->data)();
        }

        return $this->data;
    }

    /**
     * @return bool
     */
    public function showData(): bool
    {
        return $this->showData;
    }

    /**
     * @return string
     */
    public function selfUrl(): string
    {
        return \sprintf(
            '%s/relationships/%s',
            $this->resource->selfUrl(),
            Str::dasherize($this->fieldName)
        );
    }

    /**
     * @return string
     */
    public function relatedUrl(): string
    {
        return \sprintf(
            '%s/%s',
            $this->resource->selfUrl(),
            Str::dasherize($this->fieldName)
        );
    }

    /**
     * @return $this
     */
    public function withoutSelfLink(): self
    {
        $this->showSelf = false;

        return $this;
    }

    /**
     * @return $this
     */
    public function withoutRelatedLink(): self
    {
        $this->showRelated = false;

        return $this;
    }

    /**
     * @return $this
     */
    public function withoutLinks(): self
    {
        $this->withoutSelfLink();
        $this->withoutRelatedLink();

        return $this;
    }

    /**
     * @param $data
     * @return $this
     */
    public function withData($data): self
    {
        $this->data = $data;
        $this->hasData = true;

        return $this;
    }

    /**
     * Always show the data member of the relation.
     *
     * @return $this
     */
    public function alwaysShowData(): self
    {
        $this->showData = true;

        return $this;
    }

    /**
     * Always show the data member of the relation if it is loaded on the model.
     *
     * @return $this
     */
    public function showDataIfLoaded(): self
    {
        if (!$this->resource->resource instanceof Model) {
            throw new LogicException('Resource is not a model.');
        }

        $this->showData = $this->resource->resource->relationLoaded($this->keyName);

        return $this;
    }

    /**
     * @param $meta
     * @return $this
     */
    public function withMeta($meta): self
    {
        if (!is_array($meta) && !$meta instanceof Closure) {
            throw new \InvalidArgumentException('Expecting meta to be an array or a closure.');
        }

        $this->meta = $meta;

        return $this;
    }

}
