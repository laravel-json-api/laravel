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

use LaravelJsonApi\Encoder\Encoder;
use LaravelJsonApi\Encoder\Factory as EncoderFactory;

class Server
{

    /**
     * @var string
     */
    private $name;

    /**
     * Server constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('Expecting a non-empty string.');
        }

        $this->name = $name;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return Encoder
     */
    public function encoder(): Encoder
    {
        return app(EncoderFactory::class)->build(
            $this->get('resources') ?: []
        );
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    private function get(string $key, $default = null)
    {
        return config("json-api.servers.{$this->name}.{$key}", $default);
    }
}
