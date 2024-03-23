<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Laravel\Validation;

use LaravelJsonApi\Contracts\Validation\Factory as FactoryContract;
use LaravelJsonApi\Core\Document\Input\Values\ResourceType;

class Container implements \LaravelJsonApi\Contracts\Validation\Container
{
    /**
     * @inheritDoc
     */
    public function validatorsFor(string|ResourceType $resourceType): FactoryContract
    {
        return new Factory(ResourceType::cast($resourceType));
    }
}
