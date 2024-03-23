<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Laravel\Routing;

use Illuminate\Routing\RouteCollection;

class Relationships
{

    /**
     * @var RelationshipRegistrar
     */
    private RelationshipRegistrar $registrar;

    /**
     * @var array
     */
    private array $relations = [];

    /**
     * Relationships constructor.
     *
     * @param RelationshipRegistrar $registrar
     */
    public function __construct(RelationshipRegistrar $registrar)
    {
        $this->registrar = $registrar;
    }

    /**
     * Register a to-one relationship.
     *
     * @param string $fieldName
     * @return PendingRelationshipRegistration
     */
    public function hasOne(string $fieldName): PendingRelationshipRegistration
    {
        return $this->relations[$fieldName] = new PendingRelationshipRegistration(
            $this->registrar,
            $fieldName,
            false
        );
    }

    /**
     * @param string $fieldName
     * @return PendingRelationshipRegistration
     */
    public function hasMany(string $fieldName): PendingRelationshipRegistration
    {
        return $this->relations[$fieldName] = new PendingRelationshipRegistration(
            $this->registrar,
            $fieldName,
            true
        );
    }

    /**
     * @return RouteCollection
     */
    public function register(): RouteCollection
    {
        $routes = new RouteCollection();

        /** @var PendingRelationshipRegistration $registration */
        foreach ($this->relations as $registration) {
            foreach ($registration->register() as $route) {
                $routes->add($route);
            }
        }

        return $routes;
    }
}
