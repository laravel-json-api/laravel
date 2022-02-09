<?php
/*
 * Copyright 2022 Cloud Creativity Limited
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
