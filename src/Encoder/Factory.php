<?php

declare(strict_types=1);

namespace LaravelJsonApi\Encoder;

use LaravelJsonApi\Core\Resources\Container;
use LaravelJsonApi\Core\Resources\Factory as ResourceFactory;
use LaravelJsonApi\Encoder\Neomerx\Mapper;
use Neomerx\JsonApi\Factories\Factory as NeomerxFactory;

class Factory
{

    /**
     * Build a new encoder instance.
     *
     * @param array $resources
     * @return Encoder
     */
    public function build(array $resources): Encoder
    {
        $container = new Container(new ResourceFactory($resources));

        return new Encoder(
            $container,
            $factory = new NeomerxFactory(),
            new Mapper($factory)
        );
    }
}
