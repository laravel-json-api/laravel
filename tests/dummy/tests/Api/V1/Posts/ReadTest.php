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

namespace App\Tests\Api\V1\Posts;

use App\Models\Post;
use App\Models\User;
use App\Tests\Api\V1\TestCase;

class ReadTest extends TestCase
{

    public function test(): void
    {
        $post = Post::factory()->create();
        $expected = $this->serializer->post($post)->jsonSerialize();

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi()
            ->expects('posts')
            ->get(url('/api/v1/posts', $post));

        $response->assertFetchedOneExact($expected);
    }

    public function testSlugFilter(): void
    {
        $post = Post::factory()->create();
        $expected = $this->serializer->post($post)->jsonSerialize();

        $response = $this
            ->jsonApi()
            ->expects('posts')
            ->filter(['slug' => $post->slug])
            ->get(url('/api/v1/posts', $post));

        $response->assertFetchedOneExact($expected);
    }

    public function testSlugFilterDoesNotMatch(): void
    {
        $post = Post::factory()->create(['slug' => 'foo-bar']);

        $response = $this
            ->jsonApi()
            ->expects('posts')
            ->filter(['slug' => 'baz-bat'])
            ->get(url('/api/v1/posts', $post));

        $response->assertFetchedNull();
    }

    /**
     * Draft posts do not appear in our API for guests, because of our
     * post scope. Therefore, attempting to access a draft post as a
     * guest should receive a 404 response.
     */
    public function testDraftAsGuest(): void
    {
        $post = Post::factory()->create(['published_at' => null]);

        $response = $this
            ->jsonApi('posts')
            ->get(url('/api/v1/posts', $post));

        $response->assertStatus(404);
    }

    /**
     * Same if an authenticated user attempts to access the
     * draft post when they are not the author - they would receive
     * a 404 as it is excluded from the API.
     */
    public function testDraftUserIsNotAuthor(): void
    {
        $post = Post::factory()->create(['published_at' => null]);

        $response = $this
            ->actingAs(User::factory()->create())
            ->jsonApi('posts')
            ->get(url('/api/v1/posts', $post));

        $response->assertStatus(404);
    }

    /**
     * The author should be able to access their draft post.
     */
    public function testDraftAsAuthor(): void
    {
        $post = Post::factory()->create(['published_at' => null]);
        $expected = $this->serializer->post($post)->jsonSerialize();

        $response = $this
            ->actingAs($post->author)
            ->jsonApi('posts')
            ->get(url('/api/v1/posts', $post));

        $response->assertFetchedOne($expected);
    }

    public function testInvalidQueryParameter(): void
    {
        $post = Post::factory()->create();

        $response = $this
            ->jsonApi('posts')
            ->includePaths('foo')
            ->get(url('/api/v1/posts', $post));

        $response->assertExactErrorStatus([
            'detail' => 'Include path foo is not allowed.',
            'source' => ['parameter' => 'include'],
            'status' => '400',
            'title' => 'Invalid Query Parameter',
        ]);
    }

    public function testInvalidMediaType(): void
    {
        $post = Post::factory()->create();

        $this->jsonApi()
            ->accept('text/html')
            ->get(url('/api/v1/posts', $post))
            ->assertStatus(406);
    }
}
