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

class UpdateTest extends TestCase
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
        $data = $this->serialize();
        $expected = $data->forget('updatedAt')->toArray();

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi()
            ->expects('posts')
            ->withData($data)
            ->includePaths('author')
            ->patch(url('/api/v1/posts', $this->post));

        $response->assertUpdated($expected);

        $this->assertDatabaseHas('posts', [
            'author_id' => $this->post->author->getKey(),
            'content' => $data['content'],
            'created_at' => $this->post->created_at,
            'id' => $this->post->getKey(),
            'slug' => $data['slug'],
            'synopsis' => $data['synopsis'],
            'title' => $data['title'],
        ]);
    }

    /**
     * Serialize a valid post update request.
     *
     * @return ResourceObject
     */
    private function serialize(): ResourceObject
    {
        $other = Post::factory()->make();

        return ResourceObject::fromArray([
            'type' => 'posts',
            'id' => (string) $this->post->getRouteKey(),
            'attributes' => [
                'content' => $other->content,
                'createdAt' => $this->post->created_at->toJSON(),
                'slug' => $other->slug,
                'synopsis' => $other->synopsis,
                'title' => $other->title,
                'updatedAt' => $this->post->updated_at->toJSON(),
            ],
            'relationships' => [
                'author' => [
                    'data' => [
                        'type' => 'users',
                        'id' => (string) $this->post->author->getRouteKey(),
                    ],
                ],
            ],
        ]);
    }
}
