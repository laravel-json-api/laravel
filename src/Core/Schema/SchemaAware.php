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

namespace LaravelJsonApi\Core\Schema;

use LaravelJsonApi\Contracts\Schema\Container;
use LogicException;

trait SchemaAware
{

    /**
     * @var Container|null
     */
    private ?Container $schemas = null;

    /**
     * Set the container to use when looking up other schemas.
     *
     * @param Container $container
     * @return void
     */
    public function withContainer(Container $container): void
    {
        if (!$this->schemas) {
            $this->schemas = $container;
            return;
        }

        throw new LogicException('Not expecting schema container to be changed.');
    }

    /**
     * @return Container
     */
    protected function schemas(): Container
    {
        if ($this->schemas) {
            return $this->schemas;
        }

        throw new LogicException('Expecting schemas to have access to their schema container.');
    }
}
