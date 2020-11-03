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

namespace LaravelJsonApi\Http;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container as IlluminateContainer;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class ServerRepository
{

    /**
     * @var IlluminateContainer
     */
    private IlluminateContainer $container;

    /**
     * @var Repository
     */
    private Repository $config;

    /**
     * ServerRepository constructor.
     *
     * @param IlluminateContainer $container
     * @param Repository $config
     */
    public function __construct(IlluminateContainer $container, Repository $config)
    {
        $this->container = $container;
        $this->config = $config;
    }

    /**
     * @param string $name
     * @return Server
     */
    public function server(string $name): Server
    {
        if (empty($name)) {
            throw new InvalidArgumentException('Expecting a non-empty JSON API server name.');
        }

        $class = $this->config->get("json-api.servers.{$name}");

        try {
            $server = new $class($this->container, $name);
        } catch (Throwable $ex) {
            throw new RuntimeException("Unable to construct server {$name}.");
        }

        if ($server instanceof Server) {
            return $server;
        }

        throw new RuntimeException("Class for server {$name} is not a server instance.");
    }
}
