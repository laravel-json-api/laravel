<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
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
