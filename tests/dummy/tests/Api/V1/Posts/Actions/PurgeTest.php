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

namespace App\Tests\Api\V1\Posts\Actions;

use App\Models\Post;
use App\Models\User;
use App\Tests\Api\V1\TestCase;

class PurgeTest extends TestCase
{

    public function test(): void
    {
        Post::factory()->count(3)->create();

        $response = $this
            ->actingAs(User::factory()->admin()->create())
            ->jsonApi('posts')
            ->delete('/api/v1/posts/-actions/purge');

        $response->assertNoContent();

        $this->assertDatabaseCount('posts', 0);
    }

    public function testForbidden(): void
    {
        Post::factory()->count(3)->create();

        $response = $this
            ->actingAs(User::factory()->create())
            ->jsonApi('posts')
            ->delete('/api/v1/posts/-actions/purge');

        $response->assertForbidden();

        $this->assertDatabaseCount('posts', 3);
    }
}
