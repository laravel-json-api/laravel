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

namespace App\Tests\Api\V1\Users;

use App\Models\User;
use App\Tests\Api\V1\TestCase;

class ReadTest extends TestCase
{

    public function test(): void
    {
        $user = User::factory()->createOne();

        $expected = $this->serializer
            ->user($user);

        $response = $this
            ->actingAs(User::factory()->createOne())
            ->jsonApi('users')
            ->get(url('/api/v1/users', $expected['id']));

        $response->assertFetchedOneExact($expected);
    }

    public function testMe(): void
    {
        $user = User::factory()->createOne();

        $expected = $this->serializer
            ->user($user);

        $response = $this
            ->actingAs($user)
            ->jsonApi('users')
            ->get(url('/api/v1/users/me'));

        $response->assertFetchedOneExact($expected);
    }
}
