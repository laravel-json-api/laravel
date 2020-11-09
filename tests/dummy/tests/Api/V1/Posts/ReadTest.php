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

namespace App\Tests\Api\V1\Posts;

use App\Models\Post;
use App\Tests\Api\V1\TestCase;

class ReadTest extends TestCase
{

    public function test(): void
    {
        $post = Post::factory()->create();
        $expected = $this->serializer->post($post)->toArray();

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
        $expected = $this->serializer->post($post)->toArray();

        $response = $this
            ->jsonApi()
            ->expects('posts')
            ->filter(['slug' => $post->slug])
            ->get(url('/api/v1/posts', $post));

        $response->assertFetchedOneExact($expected);
    }

    public function testSlugFilterDoesNotMatch(): void
    {
        $post = Post::factory()->create();

        $response = $this
            ->jsonApi()
            ->expects('posts')
            ->filter(['slug' => 'foobar'])
            ->get(url('/api/v1/posts', $post));

        $response->assertFetchedNull();
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
