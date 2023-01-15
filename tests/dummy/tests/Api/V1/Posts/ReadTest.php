<?php
/*
 * Copyright 2023 Cloud Creativity Limited
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

use App\Models\Comment;
use App\Models\Image;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Models\Video;
use App\Tests\Api\V1\TestCase;

class ReadTest extends TestCase
{

    public function test(): void
    {
        $post = Post::factory()->create();
        $expected = $this->serializer->post($post);

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi()
            ->expects('posts')
            ->get($self = url('/api/v1/posts', $expected['id']));

        $response->assertFetchedOneExact($expected)->assertLinks(compact('self'));
    }

    public function testIncludeAuthorAndTags(): void
    {
        $post = Post::factory()
            ->has(Tag::factory()->count(2))
            ->create();

        $identifiers = $this->identifiersFor(
            'tags',
            $tags = $post->tags()->get(),
        );

        $expected = $this->serializer
            ->post($post)
            ->replace('author', $author = ['type' => 'users', 'id' => $post->author])
            ->replace('tags', $identifiers);

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi('posts')
            ->includePaths('author', 'tags')
            ->get(url('/api/v1/posts', $expected['id']));

        $response->assertFetchedOneExact($expected)->assertIncluded([
            $author,
            $identifiers[0],
            $identifiers[1],
        ]);
    }

    public function testIncludeMedia(): void
    {
        $post = Post::factory()
            ->has(Image::factory()->count(2))
            ->has(Video::factory()->count(2))
            ->create([]);

        $images = $post->images()->get();
        $videos = $post->videos()->get();

        /** @var Tag $tag */
        $tag = Tag::factory()->create();
        $tag->videos()->save($videos[0]);

        $ids = collect($images)->merge($videos)->map(fn($model) => [
            'type' => ($model instanceof Image) ? 'images' : 'videos',
            'id' => (string) $model->getRouteKey(),
        ])->all();

        $included = $ids;
        $included[] = ['type' => 'tags', 'id' => $tag->getRouteKey()];

        $expected = $this->serializer
            ->post($post)
            ->replace('media', $ids);

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi('posts')
            ->includePaths('media.tags')
            ->get(url('/api/v1/posts', $expected['id']));

        $response->assertFetchedOneExact($expected)->assertIncluded($included);
    }

    /**
     * This test exists because we had a bug in the encoder that was caused
     * by including an empty relationship.
     *
     * @see https://github.com/neomerx/json-api/issues/252
     */
    public function testIncludeEmptyTags(): void
    {
        $post = Post::factory()->create();

        $expected = $this->serializer
            ->post($post)
            ->replace('tags', []);

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi('posts')
            ->includePaths('tags')
            ->get(url('/api/v1/posts', $expected['id']));

        $response->assertFetchedOneExact($expected);
    }

    public function testSlugFilter(): void
    {
        $post = Post::factory()->create();
        $expected = $this->serializer->post($post);

        $response = $this
            ->jsonApi()
            ->expects('posts')
            ->filter(['slug' => $post->slug])
            ->get(url('/api/v1/posts', $expected['id']));

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

    public function testSparseFieldSets(): void
    {
        $post = Post::factory()->create();

        $expected = $this->serializer
            ->post($post)
            ->only('author', 'slug', 'synopsis', 'title')
            ->replace('author', ['type' => 'users', 'id' => $post->author]);

        $author = $this->serializer
            ->user($post->author)
            ->only('name');

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi('posts')
            ->sparseFields('posts', ['author', 'slug', 'synopsis', 'title'])
            ->sparseFields('users', ['name'])
            ->includePaths('author')
            ->get(url('/api/v1/posts', $expected['id']));

        $response->assertFetchedOneExact($expected)->assertIncluded([$author]);
    }

    public function testWithCount(): void
    {
        $post = Post::factory()
            ->has(Tag::factory()->count(1))
            ->has(Comment::factory()->count(3))
            ->create();

        $expected = $this->serializer
            ->post($post)
            ->withRelationshipMeta('tags', ['count' => 1])
            ->withRelationshipMeta('comments', ['count' => 3]);

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi('posts')
            ->query(['withCount' => 'comments,tags'])
            ->get(url('/api/v1/posts', $expected['id']));

        $response->assertFetchedOneExact($expected);
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
        $expected = $this->serializer->post($post);

        $response = $this
            ->actingAs($post->author)
            ->jsonApi('posts')
            ->get(url('/api/v1/posts', $expected['id']));

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

    /**
     * @param string $mediaType
     * @return void
     * @dataProvider notAcceptableMediaTypeProvider
     */
    public function testNotAcceptableMediaType(string $mediaType): void
    {
        $post = Post::factory()->create();

        $this->jsonApi()
            ->accept($mediaType)
            ->get(url('/api/v1/posts', $post))
            ->assertStatus(406);
    }
}
