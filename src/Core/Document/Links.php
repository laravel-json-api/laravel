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

use Countable;
use IteratorAggregate;
use LaravelJsonApi\Core\Contracts\Document\PaginationLinks;
use LaravelJsonApi\Core\Contracts\Serializable;
use LaravelJsonApi\Core\Json\Json;
use LogicException;
use function collect;
use function count;
use function json_encode;
use function ksort;

class Links implements Serializable, IteratorAggregate, Countable
{

    use Concerns\Serializable;

    /**
     * @var array
     */
    private $stack;

    /**
     * Create a JSON API links object.
     *
     * @param Links|Link|iterable|null $value
     * @return Links
     */
    public static function cast($value): Links
    {
        if ($value instanceof Links) {
            return $value;
        }

        if (is_null($value)) {
            return new Links();
        }

        if ($value instanceof Link) {
            return new Links($value);
        }

        if (is_array($value)) {
            return Links::fromArray($value);
        }

        throw new LogicException('Unexpected links member value.');
    }

    /**
     * @param array $input
     * @return static
     */
    public static function fromArray(array $input): self
    {
        $links = new self();

        foreach ($input as $key => $link) {
            if (!$link instanceof Link) {
                $link = Link::fromArray($key, $link);
            }

            $links->push($link);
        }

        return $links;
    }

    /**
     * Links constructor.
     *
     * @param Link ...$links
     */
    public function __construct(Link ...$links)
    {
        $this->stack = [];
        $this->push(...$links);
    }

    /**
     * Get a link by its key.
     *
     * @param string $key
     * @return Link|null
     */
    public function get(string $key): ?Link
    {
        return $this->stack[$key] ?? null;
    }

    /**
     * Push links into the collection.
     *
     * @param Link ...$links
     * @return $this
     */
    public function push(Link ...$links): self
    {
        foreach ($links as $link) {
            $this->stack[$link->key()] = $link;
        }

        ksort($this->stack);

        return $this;
    }

    /**
     * Put a link into the collection.
     *
     * @param string $key
     * @param LinkHref|string $href
     * @param mixed|null $meta
     * @return $this
     */
    public function put(string $key, $href, $meta = null)
    {
        $link = new Link($key, LinkHref::cast($href), Json::hash($meta));

        return $this->push($link);
    }

    /**
     * Add pagination links.
     *
     * @param PaginationLinks $links
     * @return $this
     */
    public function paginate(PaginationLinks $links): self
    {
        $this->put(...collect([
            $links->first(),
            $links->last(),
            $links->previous(),
            $links->next(),
        ])->filter());

        return $this;
    }

    /**
     * Merge the provided links.
     *
     * @param Links|iterable $links
     * @return $this
     */
    public function merge(iterable $links): self
    {
        foreach ($links as $key => $link) {
            if (!$link instanceof Link) {
                $link = Link::fromArray($key, $link);
            }

            $this->stack[$link->key()] = $link;
        }

        ksort($this->stack);

        return $this;
    }

    /**
     * Remove links.
     *
     * @param string ...$keys
     * @return $this
     */
    public function forget(string ...$keys): self
    {
        foreach ($keys as $key) {
            unset($this->stack[$key]);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->stack);
    }

    /**
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return collect($this->stack)->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        yield from $this->stack;
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->stack);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->stack ?: null;
    }

    /**
     * @inheritDoc
     */
    public function toJson($options = 0)
    {
        return json_encode($this, $options);
    }

}
