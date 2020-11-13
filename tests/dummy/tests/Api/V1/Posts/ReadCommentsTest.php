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

namespace App\Tests\Api\V1\Posts;

use App\Models\Comment;
use App\Models\Post;
use App\Tests\Api\V1\TestCase;

class ReadCommentsTest extends TestCase
{

    /**
     * @var Post
     */
    private Post $post;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->post = Post::factory()->create();
    }

    public function test(): void
    {
        $expected = Comment::factory()
            ->count(3)
            ->create(['post_id' => $this->post]);

        Comment::factory()
            ->for(Post::factory())
            ->create();

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi('comments')
            ->get(url('/api/v1/posts', [$this->post, 'comments']));

        $response->assertFetchedMany($expected);
    }

    public function testInvalidMediaType(): void
    {
        $this->jsonApi()
            ->accept('text/html')
            ->get(url('/api/v1/posts', [$this->post, 'comments']))
            ->assertStatus(406);
    }
}
