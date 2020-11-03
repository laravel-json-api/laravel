<?php
/**
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

namespace DummyApp\Tests\Api\V1\Posts;

use DummyApp\Post;
use DummyApp\Tests\Api\V1\TestCase;
use Illuminate\Support\Arr;

class IndexTest extends TestCase
{

    public function test(): void
    {
        $posts = Post::factory()->count(3)->create();

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi()
            ->expects('posts')
            ->get('/api/v1/posts');

        $response->assertFetchedMany($posts);
    }

    public function testPaginated(): void
    {
        $posts = Post::factory()->count(5)->create();

        $meta = [
            'current_page' => 1,
            'from' => 1,
            'last_page' => 2,
            'per_page' => 3,
            'to' => 3,
            'total' => 5,
        ];

        $links = [
            'first' => 'http://localhost/api/v1/posts?' . Arr::query(['page' => ['number' => 1, 'size' => 3]]),
            'next' => 'http://localhost/api/v1/posts?' . Arr::query(['page' => ['number' => 2, 'size' => 3]]),
            'last' => 'http://localhost/api/v1/posts?' . Arr::query(['page' => ['number' => 2, 'size' => 3]]),
        ];

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi()
            ->expects('posts')
            ->page(['number' => 1, 'size' => 3])
            ->get('/api/v1/posts');

        $response->assertFetchedMany($posts->take(3))
            ->assertMeta($meta)
            ->assertLinks($links);
    }

    public function testIncludeAuthor(): void
    {
        $posts = Post::factory()->count(2)->create();

        $expected1 = $this->serializer->post($posts[0])->toArray();
        $expected1['relationships']['author']['data'] = $user1 = [
            'type' => 'users',
            'id' => (string) $posts[0]->author->getRouteKey(),
        ];

        $expected2 = $this->serializer->post($posts[1])->toArray();
        $expected2['relationships']['author']['data'] = $user2 = [
            'type' => 'users',
            'id' => (string) $posts[1]->author->getRouteKey(),
        ];

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi()
            ->expects('posts')
            ->includePaths('author')
            ->get('/api/v1/posts');

        $response->assertFetchedMany([$expected1, $expected2])->assertIncluded([
            $user1,
            $user2,
        ]);
    }
}
