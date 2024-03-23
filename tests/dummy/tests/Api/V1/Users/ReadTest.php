<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
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
