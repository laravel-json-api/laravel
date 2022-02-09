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

namespace LaravelJsonApi\Laravel\Tests\Unit;

use LaravelJsonApi\Core\Facades\JsonApi;
use LaravelJsonApi\Laravel\Facades\JsonApiRoute;
use LaravelJsonApi\Laravel\ServiceProvider;
use PHPUnit\Framework\TestCase;

class PackageTest extends TestCase
{

    public function test(): void
    {
        $json = json_decode(file_get_contents(__DIR__ . '/../../../composer.json'), true);

        $this->assertArrayHasKey('laravel', $json['extra']);
        $this->assertSame([
            'aliases' => [
                'JsonApi' => JsonApi::class,
                'JsonApiRoute' => JsonApiRoute::class,
            ],
            'providers' => [
                ServiceProvider::class,
            ],
        ], $json['extra']['laravel']);
    }
}
