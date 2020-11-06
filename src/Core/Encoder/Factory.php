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

use LaravelJsonApi\Contracts\Resources\Factory as ResourceFactoryContract;
use LaravelJsonApi\Core\Encoder\Neomerx\Mapper;
use LaravelJsonApi\Core\Resources\Container;
use Neomerx\JsonApi\Factories\Factory as NeomerxFactory;

class Factory
{

    /**
     * Build a new encoder instance.
     *
     * @param ResourceFactoryContract ...$factories
     * @return Encoder
     */
    public function build(ResourceFactoryContract ...$factories): Encoder
    {
        return new Encoder(
            new Container(...$factories),
            $factory = new NeomerxFactory(),
            new Mapper($factory)
        );
    }
}
