<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use LaravelJsonApi\Laravel\Routing\PendingServerRegistration;
use LaravelJsonApi\Laravel\Routing\Registrar;

/**
 * Class JsonApiRoute
 *
 * @method static PendingServerRegistration server(string $name)
 */
class JsonApiRoute extends Facade
{

    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Registrar::class;
    }
}
