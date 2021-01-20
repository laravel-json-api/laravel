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

namespace App\Tests\Api\V1\Posts;

use App\Models\Post;
use App\Models\Tag;
use App\Tests\Api\V1\TestCase;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use LaravelJsonApi\Core\Document\ResourceObject;

class CreateTest extends TestCase
{

    /**
     * @var EloquentCollection
     */
    private EloquentCollection $tags;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->tags = Tag::factory()->count(3)->create();
    }

    public function test(): void
    {
        $post = Post::factory()->make();
        $data = $this->serialize($post);

        $expected = $data
            ->forget('createdAt', 'updatedAt')
            ->replace('author', ['type' => 'users', 'id' => (string) $post->author->getRouteKey()])
            ->jsonSerialize();

        $response = $this
            ->withoutExceptionHandling()
            ->actingAs($post->author)
            ->jsonApi('posts')
            ->withData($data)
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

        $this->assertDatabaseCount('taggables', 2);

        /** @var Tag $tag */
        foreach ($this->tags->take(2) as $tag) {
            $this->assertDatabaseHas('taggables', [
                'tag_id' => $tag->getKey(),
                'taggable_id' => $id,
                'taggable_type' => Post::class,
            ]);
        }
    }

    public function testInvalid(): void
    {
        $exists = Post::factory()->create();
        $post = Post::factory()->make();

        $data = $this
            ->serialize($post)
            ->replace('slug', $exists->slug)
            ->jsonSerialize();

        $expected = [
            'detail' => 'The slug has already been taken.',
            'source' => ['pointer' => '/data/attributes/slug'],
            'status' => '422',
            'title' => 'Unprocessable Entity',
        ];

        $response = $this
            ->actingAs($post->author)
            ->jsonApi('posts')
            ->withData($data)
            ->post('/api/v1/posts');

        $response->assertExactErrorStatus($expected);
    }

    public function testClientId(): void
    {
        $post = Post::factory()->make();

        $data = $this
            ->serialize($post)
            ->withId('81166677-f3c4-440c-9a4a-12b89802d731')
            ->jsonSerialize();

        $expected = [
            'detail' => "Resource type posts does not support client-generated IDs.",
            'source' => ['pointer' => '/data/id'],
            'status' => '403',
            'title' => 'Not Supported',
        ];

        $response = $this
            ->actingAs($post->author)
            ->jsonApi('posts')
            ->withData($data)
            ->post('/api/v1/posts');

        $response->assertExactErrorStatus($expected);
    }

    public function testUnauthorized(): void
    {
        $post = Post::factory()->make();
        $data = $this->serialize($post);

        $response = $this
            ->jsonApi('posts')
            ->withData($data)
            ->post('/api/v1/posts');

        $response->assertStatus(401);
        $this->assertDatabaseCount('posts', 0);
    }

    public function testNotAcceptableMediaType(): void
    {
        $post = Post::factory()->make();
        $data = $this->serialize($post);

        $response = $this
            ->actingAs($post->author)
            ->jsonApi('posts')
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
            ->jsonApi('posts')
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
                'tags' => [
                    'data' => $this->tags->take(2)->map(fn(Tag $tag) => [
                        'type' => 'tags',
                        'id' => (string) $tag->getRouteKey(),
                    ])->all(),
                ],
            ],
        ]);
    }
}
