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

namespace DummyApp\Tests\Api\V1\Posts;

use DummyApp\Post;
use DummyApp\Tests\Api\V1\TestCase;

class ReadTest extends TestCase
{

    public function test(): void
    {
        $post = factory(Post::class)->create();
        $expected = $this->serializer->post($post)->toArray();

        $response = $this
            ->jsonApi()
            ->expects('posts')
            ->get(url('/api/v1/posts', $post));

        $response->assertFetchedOneExact($expected);
    }

    public function testInvalidMediaType(): void
    {
        $post = factory(Post::class)->create();

        $this->jsonApi()
            ->accept('text/html')
            ->get(url('/api/v1/posts', $post))
            ->assertStatus(406);
    }
}
