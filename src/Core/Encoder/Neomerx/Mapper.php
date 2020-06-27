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

namespace LaravelJsonApi\Core\Encoder\Neomerx;

use Generator;
use LaravelJsonApi\Core\Document\Link;
use LaravelJsonApi\Core\Document\Links;
use LaravelJsonApi\Core\Document\ResourceIdentifier;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neomerx\JsonApi\Contracts\Schema\IdentifierInterface;
use Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use Neomerx\JsonApi\Schema\Identifier;
use function iterator_to_array;

class Mapper
{

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * Mapper constructor.
     *
     * @param FactoryInterface $factory
     */
    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Convert a Laravel JSON API resource identifier to a Neomerx identifier.
     *
     * @param ResourceIdentifier $identifier
     * @return IdentifierInterface
     */
    public function identifier(ResourceIdentifier $identifier): IdentifierInterface
    {
        return new Identifier(
            $identifier->id(),
            $identifier->type(),
            $meta = $identifier->hasMeta(),
            $meta ? $identifier->meta() : null
        );
    }

    /**
     * Convert Laravel JSON API links to Neomerx links.
     *
     * @param Links $links
     * @return Generator
     */
    public function links(Links $links): Generator
    {
        /** @var Link $link */
        foreach ($links as $link) {
            yield $link->key() => $this->link($link);
        }
    }

    /**
     * Convert Laravel JSON API links to an array of Neomerx links.
     *
     * @param Links $links
     * @return array
     */
    public function allLinks(Links $links): array
    {
        return iterator_to_array($this->links($links));
    }

    /**
     * Convert a Laravel JSON API link to a Neomerx link.
     *
     * @param Link $link
     * @return LinkInterface
     */
    public function link(Link $link): LinkInterface
    {
        return $this->factory->createLink(
            false,
            $link->href()->toString(),
            $meta = $link->hasMeta(),
            $meta ? $link->meta() : null
        );
    }
}
