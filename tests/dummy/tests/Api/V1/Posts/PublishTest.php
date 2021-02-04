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

declare(strict_types=1);

namespace App\Tests\Api\V1\Posts;

use App\Models\Post;
use App\Tests\Api\V1\TestCase;

class PublishTest extends TestCase
{

    public function test(): void
    {
        $this->travelTo($date = now()->milliseconds(0));

        $post = Post::factory()->create(['published_at' => null]);

        $expected = $this->serializer
            ->post($post)
            ->replace('publishedAt', $date->jsonSerialize())
            ->jsonSerialize();

        $response = $this
            ->withoutExceptionHandling()
            ->actingAs($post->author)
            ->jsonApi('posts')
            ->contentType('application/json')
            ->post(url('/api/v1/posts', [$post, '-actions/publish']));

        $response->assertFetchedOneExact($expected);

        $this->assertDatabaseHas('posts', array_replace(
            $post->getAttributes(),
            ['published_at' => $date->toDateTimeString()]
        ));
    }
}
