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

namespace LaravelJsonApi\Core\Responses\Concerns;

use Illuminate\Http\Request;
use LaravelJsonApi\Core\Document\JsonApi;
use LaravelJsonApi\Core\Document\Link;
use LaravelJsonApi\Core\Document\Links;
use LaravelJsonApi\Core\Facades\JsonApi as JsonApiFacade;
use LaravelJsonApi\Core\Json\Hash;
use LaravelJsonApi\Core\Query\FieldSets;
use LaravelJsonApi\Core\Query\IncludePaths;

trait IsResponsable
{

    /**
     * @var JsonApi|null
     */
    private ?JsonApi $jsonApi = null;

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
     * @var array
     */
    private array $headers = [];

    /**
     * Add the top-level JSON API member to the response.
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
     * @return JsonApi
     */
    public function jsonApi(): JsonApi
    {
        if ($this->jsonApi) {
            return $this->jsonApi;
        }

        return $this->jsonApi = JsonApiFacade::server()->jsonApi();
    }

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
     * @return Hash
     */
    public function meta(): Hash
    {
        if ($this->meta) {
            return $this->meta;
        }

        return $this->meta = new Hash();
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
     * @return Links
     */
    public function links(): Links
    {
        if ($this->links) {
            return $this->links;
        }

        return $this->links = new Links();
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
     * @return array
     */
    protected function headers(): array
    {
        return \collect(['Content-Type' => 'application/vnd.api+json'])
            ->merge($this->headers ?: [])
            ->all();
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
