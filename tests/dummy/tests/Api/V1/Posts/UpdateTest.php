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
use App\Models\Tag;
use App\Models\User;
use App\Tests\Api\V1\TestCase;
use Illuminate\Support\Facades\Date;
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
        $this->post->tags()->saveMany(
            Tag::factory()->count(2)->create()
        );

        $tags = Tag::factory()->count(1)->create();

        $data = $this
            ->serialize()
            ->replace('tags', $this->identifiersFor('tags', $tags));

        $expected = $data->forget('updatedAt')->jsonSerialize();

        $response = $this
            ->actingAs($this->post->author)
            ->jsonApi('posts')
            ->withData($data)
            ->includePaths('author', 'tags')
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

        $this->assertDatabaseCount('taggables', count($tags));

        foreach ($tags as $tag) {
            $this->assertDatabaseHas('taggables', [
                'tag_id' => $tag->getKey(),
                'taggable_type' => Post::class,
                'taggable_id' => $this->post->getKey(),
            ]);
        }
    }

    /**
     * @return array
     */
    public function fieldProvider(): array
    {
        return [
            ['content'],
            ['slug'],
            ['synopsis'],
            ['title'],
        ];
    }

    /**
     * @param string $fieldName
     * @dataProvider fieldProvider
     */
    public function testIndividualField(string $fieldName): void
    {
        $data = $this->serialize()->only($fieldName);

        $expected = $this->serializer
            ->post($this->post)
            ->forget('updatedAt')
            ->replace($fieldName, $data[$fieldName]);

        $response = $this
            ->withoutExceptionHandling()
            ->actingAs($this->post->author)
            ->jsonApi('posts')
            ->withData($data)
            ->patch(url('/api/v1/posts', $this->post));

        $response->assertUpdated($expected->jsonSerialize());

        $this->assertDatabaseHas('posts', [
            'author_id' => $this->post->author->getKey(),
            'content' => $expected['content'],
            'created_at' => $this->post->created_at,
            'id' => $this->post->getKey(),
            'slug' => $expected['slug'],
            'synopsis' => $expected['synopsis'],
            'title' => $expected['title'],
        ]);
    }

    public function testSoftDelete(): void
    {
        $deleted = false;

        Post::deleted(function () use (&$deleted) {
            $deleted = true;
        });

        $date = Date::yesterday()->startOfSecond();

        $data = $this->serialize()
            ->only('deletedAt')
            ->replace('deletedAt', $date->toJSON());

        $expected = $this->serializer
            ->post($this->post)
            ->forget('updatedAt')
            ->replace('deletedAt', $date->toJSON());

        $response = $this
            ->withoutExceptionHandling()
            ->actingAs($this->post->author)
            ->jsonApi('posts')
            ->withData($data)
            ->patch(url('/api/v1/posts', $this->post));

        $response->assertUpdated($expected->jsonSerialize());

        $this->assertSoftDeleted($this->post);
        $this->assertTrue($deleted);
    }

    public function testRestore(): void
    {
        $this->post->forceFill(['deleted_at' => Date::now()])->save();

        $restored = false;

        Post::restored(function () use (&$restored) {
            $restored = true;
        });

        $data = $this->serialize()
            ->only('deletedAt')
            ->replace('deletedAt', null);

        $expected = $this->serializer
            ->post($this->post)
            ->forget('updatedAt')
            ->replace('deletedAt', null);

        $response = $this
            ->withoutExceptionHandling()
            ->actingAs($this->post->author)
            ->jsonApi('posts')
            ->withData($data)
            ->patch(url('/api/v1/posts', $this->post));

        $response->assertUpdated($expected->jsonSerialize());

        $this->assertDatabaseHas('posts', array_merge($this->post->getOriginal(), [
            'deleted_at' => null,
        ]));

        $this->assertTrue($restored);
    }

    public function testInvalid(): void
    {
        $other = Post::factory()->create();

        $data = $this->serialize()->replace('slug', $other->slug);

        $response = $this
            ->actingAs($this->post->author)
            ->jsonApi('posts')
            ->withData($data)
            ->includePaths('author')
            ->patch(url('/api/v1/posts', $this->post));

        $response->assertExactErrorStatus([
            'detail' => 'The slug has already been taken.',
            'source' => ['pointer' => '/data/attributes/slug'],
            'status' => '422',
            'title' => 'Unprocessable Entity',
        ]);
    }

    public function testUnauthorized(): void
    {
        $response = $this
            ->jsonApi('posts')
            ->withData($this->serialize())
            ->includePaths('author')
            ->patch(url('/api/v1/posts', $this->post));

        $response->assertStatus(401);
    }

    public function testForbidden(): void
    {
        $response = $this
            ->actingAs(User::factory()->create())
            ->jsonApi('posts')
            ->withData($this->serialize())
            ->includePaths('author')
            ->patch(url('/api/v1/posts', $this->post));

        $response->assertStatus(403);
    }

    public function testNotAcceptableMediaType(): void
    {
        $data = $this->serialize();

        $response = $this
            ->actingAs($this->post->author)
            ->jsonApi('posts')
            ->accept('text/html')
            ->withData($data)
            ->patch(url('/api/v1/posts', $this->post));

        $response->assertStatus(406);
        $this->assertDatabaseHas('posts', $this->post->getAttributes());
    }

    public function testUnsupportedMediaType(): void
    {
        $data = $this->serialize();

        $response = $this
            ->actingAs($this->post->author)
            ->jsonApi()
            ->expects('posts')
            ->contentType('application/json')
            ->withData($data)
            ->patch(url('/api/v1/posts', $this->post));

        $response->assertStatus(415);
        $this->assertDatabaseHas('posts', $this->post->getAttributes());
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
                'deletedAt' => optional($this->post->deleted_at)->toJSON(),
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
                'tags' => [
                    'data' => $this->identifiersFor('tags', $this->post->tags),
                ],
            ],
        ]);
    }
}
