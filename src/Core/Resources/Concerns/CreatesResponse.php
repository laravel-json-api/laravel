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

namespace LaravelJsonApi\Core\Resources\Concerns;

use Illuminate\Http\Request;
use LaravelJsonApi\Core\Document\Links;
use LaravelJsonApi\Core\Json\Hash;
use LaravelJsonApi\Core\Query\FieldSets;
use LaravelJsonApi\Core\Query\IncludePaths;

trait CreatesResponse
{

    /**
     * @var Hash|null
     */
    private ?Hash $meta = null;

    /**
     * @var Links|null
     */
    private ?Links $links = null;

    /**
     * @var int
     */
    private int $encodeOptions = 0;

    /**
     * @var array|null
     */
    private ?array $headers = null;

    /**
     * Add top-level meta to the response.
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
     * Add top-level links to the response.
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
     * Set JSON encode options.
     *
     * @param int $options
     * @return $this
     */
    public function withEncodeOptions(int $options): self
    {
        $this->encodeOptions = $options;

        return $this;
    }

    /**
     * Set response headers.
     *
     * @param array $headers
     * @return $this
     */
    public function withHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @param Request $request
     * @return IncludePaths|null
     */
    protected function includePaths($request): ?IncludePaths
    {
        if ($request->query->has('include')) {
            return IncludePaths::fromString($request->query('include') ?: '');
        }

        return null;
    }

    /**
     * @param Request $request
     * @return FieldSets|null
     */
    protected function fieldSets($request): ?FieldSets
    {
        if ($request->query->has('fields')) {
            return FieldSets::fromArray($request->query('fields') ?: []);
        }

        return null;
    }
}
