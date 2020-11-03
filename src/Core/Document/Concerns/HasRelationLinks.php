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

namespace LaravelJsonApi\Core\Document\Concerns;

use LaravelJsonApi\Core\Document\Link;
use LaravelJsonApi\Core\Document\LinkHref;
use LaravelJsonApi\Core\Document\Links;
use function sprintf;

/**
 * Trait HasRelationLinks
 *
 * @TODO not sure this is in use?
 */
trait HasRelationLinks
{

    /**
     * @var string
     */
    protected string $fieldName;

    /**
     * @var string
     */
    protected string $baseUri;

    /**
     * @var Links|null
     */
    private ?Links $links = null;

    /**
     * @return Links
     */
    public function links(): Links
    {
        if ($this->links) {
            return $this->links;
        }

        return $this->links = new Links(
            $this->selfLink(),
            $this->relatedLink()
        );
    }

    /**
     * @return bool
     */
    public function hasLinks(): bool
    {
        return $this->links()->isNotEmpty();
    }

    /**
     * @return string
     */
    protected function selfUrl(): string
    {
        return sprintf('%s/relationships/%s', $this->baseUri, $this->fieldName);
    }

    /**
     * @return Link
     */
    protected function selfLink(): Link
    {
        return new Link('self', new LinkHref($this->selfUrl()));
    }

    /**
     * @return string
     */
    protected function relatedUrl(): string
    {
        return sprintf('%s/%s', $this->baseUri, $this->fieldName);
    }

    /**
     * @return Link
     */
    protected function relatedLink(): Link
    {
        return new Link('related', new LinkHref($this->relatedUrl()));
    }
}
