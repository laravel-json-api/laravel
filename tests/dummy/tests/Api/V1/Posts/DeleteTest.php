<?php
/*
 * Copyright 2023 Cloud Creativity Limited
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
use App\Models\User;
use App\Tests\Api\V1\TestCase;

class DeleteTest extends TestCase
{

    /**
     * @var Post
     */
    private Post $post;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->post = Post::factory()->create();
    }

    public function test(): void
    {
        $response = $this
            ->withoutExceptionHandling()
            ->actingAs($this->post->author)
            ->jsonApi()
            ->delete(url('api/v1/posts', $this->post));

        $response->assertNoContent();

        $this->assertDatabaseMissing('posts', [
            'id' => $this->post->getKey(),
        ]);
    }

    public function testCannotDeletePostWithComments(): void
    {
        Comment::factory()->create(['post_id' => $this->post]);

        $response = $this
            ->actingAs($this->post->author)
            ->jsonApi()
            ->delete(url('api/v1/posts', $this->post));

        $response->assertExactErrorStatus([
            'detail' => 'Cannot delete a post with comments.',
            'status' => '422',
            'title' => 'Not Deletable',
        ]);

        $this->assertDatabaseHas('posts', [
            'id' => $this->post->getKey(),
        ]);
    }

    public function testUnauthorized(): void
    {
        $response = $this
            ->jsonApi()
            ->delete(url('api/v1/posts', $this->post));

        $response->assertStatus(401);

        $this->assertDatabaseHas('posts', [
            'id' => $this->post->getKey(),
        ]);
    }

    public function testForbidden(): void
    {
        $response = $this
            ->actingAs(User::factory()->create())
            ->jsonApi()
            ->delete(url('api/v1/posts', $this->post));

        $response->assertStatus(403);

        $this->assertDatabaseHas('posts', [
            'id' => $this->post->getKey(),
        ]);
    }

    /**
     * @param string $mediaType
     * @return void
     * @dataProvider notAcceptableMediaTypeProvider
     */
    public function testNotAcceptableMediaType(string $mediaType): void
    {
        $response = $this
            ->actingAs($this->post->author)
            ->jsonApi()
            ->accept($mediaType)
            ->delete(url('api/v1/posts', $this->post));

        $response->assertStatus(406);
    }
}
