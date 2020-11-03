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

namespace LaravelJsonApi\Core\Document;

use JsonSerializable;
use LogicException;
use UnexpectedValueException;
use function collect;
use function http_build_query;
use function is_null;
use function is_string;
use function sprintf;

class LinkHref implements JsonSerializable
{

    /**
     * @var string
     */
    private string $uri;

    /**
     * @var array|null
     */
    private ?array $query = null;

    /**
     * @param LinkHref|static|string $value
     * @return static
     */
    public static function cast($value): self
    {
        if ($value instanceof static) {
            return $value;
        }

        if (is_string($value)) {
            return new self($value);
        }

        throw new LogicException('Unexpected link href.');
    }

    /**
     * LinkHref constructor.
     *
     * @param string $uri
     * @param iterable|null $query
     */
    public function __construct(string $uri, iterable $query = null)
    {
        if (empty($uri)) {
            throw new UnexpectedValueException('Expecting a non-empty string URI.');
        }

        $this->uri = $uri;

        if (!is_null($query)) {
            $this->withQuery($query);
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        if ($this->query) {
            return sprintf('%s?%s', $this->uri, http_build_query($this->query));
        }

        return $this->uri;
    }

    /**
     * @param iterable $query
     * @return $this
     */
    public function withQuery(iterable $query): self
    {
        $this->query = collect($query)->toArray() ?: null;

        return $this;
    }

    /**
     * @return $this
     */
    public function withoutQuery(): self
    {
        $this->query = null;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->toString();
    }

}
