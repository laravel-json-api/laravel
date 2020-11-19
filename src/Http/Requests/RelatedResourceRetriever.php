<?php
/*
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

namespace LaravelJsonApi\Laravel\Http\Requests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use LaravelJsonApi\Contracts\Schema\PolymorphicRelation;
use LaravelJsonApi\Contracts\Schema\Relation;
use LaravelJsonApi\Contracts\Server\Server;
use LaravelJsonApi\Core\Support\Arr;

class RelatedResourceRetriever
{

    /**
     * @var Server
     */
    private Server $server;

    /**
     * @var Relation
     */
    protected Relation $relation;

    /**
     * @var Request
     */
    protected Request $request;

    /**
     * RelatedResource constructor.
     *
     * @param Server $server
     * @param Relation $relation
     * @param Request $request
     */
    public function __construct(Server $server, Relation $relation, Request $request)
    {
        $this->server = $server;
        $this->relation = $relation;
        $this->request = $request;
    }

    /**
     * Find the related resource(s).
     *
     * @return Model|object|Collection|null
     */
    public function __invoke()
    {
        if ($this->relation->toOne()) {
            return $this->toOne();
        }

        return $this->toMany();
    }

    /**
     * Retrieve the related resource for a to-one relation.
     *
     * @return object|null
     */
    private function toOne(): ?object
    {
        $data = $this->request->json('data');

        if ($this->isValid($data)) {
            return $this->server->store()->find(
                $data['type'],
                $data['id']
            );
        }

        return null;
    }

    /**
     * @return Collection
     */
    private function toMany(): Collection
    {
        $data = $this->request->json('data');
        $identifiers = [];

        if (is_array($data) && !Arr::isAssoc($data)) {
            $identifiers = collect($data)
                ->filter(fn($value) => $this->isValid($value))
                ->all();
        }

        return collect($this->server->store()->findMany($identifiers));
    }

    /**
     * @param mixed $identifier
     * @return bool
     */
    private function isValid($identifier): bool
    {
        if (is_array($identifier) && isset($identifier['type']) && isset($identifier['id'])) {
            return $this->isType($identifier['type']) && $this->isId($identifier['id']);
        }

        return false;
    }

    /**
     * @param mixed $type
     * @return bool
     */
    private function isType($type): bool
    {
        return in_array($type, $this->expects(), true);
    }

    /**
     * @param mixed $id
     * @return bool
     */
    private function isId($id): bool
    {
        if (is_string($id)) {
            return !empty($id) || '0' === $id;
        }

        return false;
    }

    /**
     * @return array
     */
    private function expects(): array
    {
        if ($this->relation instanceof PolymorphicRelation) {
            return $this->relation->inverseTypes();
        }

        return [$this->relation->inverse()];
    }

}
