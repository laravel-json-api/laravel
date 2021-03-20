<?php
/*
 * Copyright 2021 Cloud Creativity Limited
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

namespace App\Tests\Api\V1;

use App\Tests\TestCase as BaseTestCase;
use Hashids\Hashids;
use Illuminate\Contracts\Routing\UrlRoutable;
use LaravelJsonApi\Testing\MakesJsonApiRequests;

class TestCase extends BaseTestCase
{

    use MakesJsonApiRequests;

    /**
     * @var Serializer
     */
    protected Serializer $serializer;

    /**
     * @var Hashids
     */
    protected Hashids $hashIds;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->hashIds = \Vinkla\Hashids\Facades\Hashids::connection();
        $this->serializer = new Serializer($this->hashIds);
    }

    /**
     * @param $modelOrResourdId
     * @return string
     */
    protected function hashId($modelOrResourdId): string
    {
        if ($modelOrResourdId instanceof UrlRoutable) {
            $modelOrResourdId = $modelOrResourdId->getRouteKey();
        }

        return $this->hashIds->encode($modelOrResourdId);
    }

    /**
     * @param $modelsOrResourceIds
     * @return array
     */
    protected function hashIds($modelsOrResourceIds): array
    {
        return collect($modelsOrResourceIds)
            ->map(fn($modelOrResourceId) => $this->hashId($modelOrResourceId))
            ->values()
            ->all();
    }

    /**
     * @param string $type
     * @param $modelsOrResourceIds
     * @return array
     */
    protected function hashIdentifiers(string $type, $modelsOrResourceIds): array
    {
        return collect($modelsOrResourceIds)->map(fn($modelOrResourceId) => [
            'type' => $type,
            'id' => $this->hashId($modelOrResourceId),
        ])->values()->all();
    }
}
