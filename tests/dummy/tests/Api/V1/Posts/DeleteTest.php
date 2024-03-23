<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
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
