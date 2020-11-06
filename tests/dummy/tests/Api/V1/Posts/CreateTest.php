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
use LaravelJsonApi\Core\Document\ResourceObject;

class CreateTest extends TestCase
{

    public function test(): void
    {
        $post = Post::factory()->make();
        $data = $this->serialize($post);

        $expected = $data
            ->forget('createdAt', 'updatedAt')
            ->replace('author', ['type' => 'users', 'id' => (string) $post->author->getRouteKey()])
            ->toArray();

        $response = $this
            ->withoutExceptionHandling()
            ->actingAs($post->author)
            ->jsonApi()
            ->expects('posts')
            ->withData($data)
            ->includePaths('author')
            ->post('/api/v1/posts');

        $id = $response
            ->assertCreatedWithServerId(url('/api/v1/posts'), $expected)
            ->id();

        $this->assertDatabaseHas('posts', [
            'author_id' => $post->author->getKey(),
            'content' => $data['content'],
            'id' => $id,
            'slug' => $data['slug'],
            'synopsis' => $data['synopsis'],
            'title' => $data['title'],
        ]);
    }

    public function testNotAcceptableMediaType(): void
    {
        $post = Post::factory()->make();
        $data = $this->serialize($post);

        $response = $this
            ->actingAs($post->author)
            ->jsonApi()
            ->expects('posts')
            ->accept('text/html')
            ->withData($data)
            ->post('/api/v1/posts');

        $response->assertStatus(406);
        $this->assertDatabaseMissing('posts', []);
    }

    public function testUnsupportedMediaType(): void
    {
        $post = Post::factory()->make();
        $data = $this->serialize($post);

        $response = $this
            ->actingAs($post->author)
            ->jsonApi()
            ->expects('posts')
            ->contentType('application/json')
            ->withData($data)
            ->post('/api/v1/posts');

        $response->assertStatus(415);
        $this->assertDatabaseMissing('posts', []);
    }

    /**
     * Serialize the post model for a valid create request.
     *
     * @param Post $post
     * @return ResourceObject
     */
    private function serialize(Post $post): ResourceObject
    {
        return ResourceObject::fromArray([
            'type' => 'posts',
            'attributes' => [
                'content' => $post->content,
                'createdAt' => null,
                'slug' => $post->slug,
                'synopsis' => $post->synopsis,
                'title' => $post->title,
                'updatedAt' => null,
            ],
            'relationships' => [
                'author' => [
                    'data' => null,
                ],
            ],
        ]);
    }
}
