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

namespace LaravelJsonApi\Core\Encoder;

use LaravelJsonApi\Core\Contracts\Serializable;
use LaravelJsonApi\Core\Document\JsonApi;
use LaravelJsonApi\Core\Document\Links;
use LaravelJsonApi\Core\Encoder\Neomerx\Mapper;
use LaravelJsonApi\Core\Json\Hash;

class CompoundDocument implements Serializable
{

    /**
     * @var Neomerx\Encoder
     */
    private $encoder;

    /**
     * @var Mapper
     */
    private $mapper;

    /**
     * @var mixed
     */
    private $data;

    /**
     * @var JsonApi
     */
    private $jsonApi;

    /**
     * @var Links|null
     */
    private $links;

    /**
     * @var Hash|null
     */
    private $meta;

    /**
     * CompoundDocument constructor.
     *
     * @param Neomerx\Encoder $encoder
     * @param Mapper $mapper
     * @param mixed $data
     */
    public function __construct(Neomerx\Encoder $encoder, Mapper $mapper, $data)
    {
        $this->encoder = $encoder;
        $this->mapper = $mapper;
        $this->data = $data;
        $this->jsonApi = new JsonApi('1.0');
    }

    /**
     * Set the top-level JSON API member.
     *
     * @param $jsonApi
     * @return $this
     */
    public function withJsonApi($jsonApi): self
    {
        $this->jsonApi = JsonApi::cast($jsonApi);

        return $this;
    }

    /**
     * Set the top-level links member.
     *
     * @param $links
     * @return $this
     */
    public function withLinks($links): self
    {
        $this->links = Links::cast($links);

        return $this;
    }

    /**
     * Set the top-level meta member.
     *
     * @param $meta
     * @return $this
     */
    public function withMeta($meta): self
    {
        $this->meta = Hash::cast($meta);

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return $this->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        try {
            return json_decode($this->toJson(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $ex) {
            throw new \LogicException('Unable to convert document to an array.', 0, $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        try {
            $this->prepareEncoder();

            return $this->encoder->serializeData($this->data);
        } catch (\Throwable $ex) {
            throw new \LogicException('Unable to serialize compound document.', 0, $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function toJson($options = 0)
    {
        try {
            $this->prepareEncoder();

            return $this->encoder
                ->withEncodeOptions($options | JSON_THROW_ON_ERROR)
                ->encodeData($this->data);
        } catch (\Throwable $ex) {
            throw new \LogicException('Unable to encode compound document.', 0, $ex);
        }
    }

    /**
     * Reset the encoder.
     *
     * @return void
     */
    private function prepareEncoder(): void
    {
        if ($version = $this->jsonApi->version()) {
            $this->encoder->withJsonApiVersion($version);
        }

        if ($this->jsonApi->hasMeta()) {
            $this->encoder->withJsonApiMeta($this->jsonApi->meta());
        }

        if ($this->meta && $this->meta->isNotEmpty()) {
            $this->encoder->withMeta($this->meta);
        }

        if ($this->links && $this->links->isNotEmpty()) {
            $this->encoder->withLinks($this->mapper->allLinks($this->links));
        }
    }

}
