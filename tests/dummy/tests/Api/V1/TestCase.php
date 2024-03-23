<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace App\Tests\Api\V1;

use App\Tests\TestCase as BaseTestCase;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Support\Collection;
use LaravelJsonApi\Testing\MakesJsonApiRequests;

class TestCase extends BaseTestCase
{

    use MakesJsonApiRequests;

    /**
     * @var Serializer
     */
    protected Serializer $serializer;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->serializer = new Serializer();
    }

    /**
     * @return array
     */
    public static function notAcceptableMediaTypeProvider(): array
    {
        return [
            'application/json' => ['application/json'],
            'text/html' => ['text/html'],
        ];
    }

    /**
     * @param string $type
     * @param $modelsOrResourceIds
     * @return array
     */
    protected function identifiersFor(string $type, $modelsOrResourceIds): array
    {
        return Collection::make($modelsOrResourceIds)->map(fn($modelOrResourceId) => [
            'type' => $type,
            'id' => ($modelOrResourceId instanceof UrlRoutable) ?
                (string) $modelOrResourceId->getRouteKey() :
                (string) $modelOrResourceId
        ])->values()->all();
    }
}
