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

declare(strict_types=1);

namespace App\Tests\Api\V1\Posts;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Tests\Api\V1\TestCase;
use Faker\Generator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;

class IndexTest extends TestCase
{

    public function test(): void
    {
        $posts = Post::factory()->sequence(
            ['created_at' => Date::now()->subWeek()],
            ['created_at' => Date::yesterday()],
            ['created_at' => Date::now()],
        )->count(3)->create();

        $expected = $posts
            ->sortByDesc('created_at')
            ->values()
            ->all();

        /** Draft post should not appear. */
        Post::factory()->create(['published_at' => null]);

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi()
            ->expects('posts')
            ->get('/api/v1/posts');

        $response->assertFetchedManyInOrder($expected);
    }

    public function testWithUser(): void
    {
        $user = User::factory()->create();
        $posts = Post::factory()->count(3)->create();

        $expected = $this->identifiersFor('posts', $posts);

        /** Draft for this user should appear. */
        Post::factory()->create([
            'author_id' => $user,
            'published_at' => null,
        ]);

        /** Draft post should not appear. */
        Post::factory()->create(['published_at' => null]);

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi()
            ->expects('posts')
            ->get('/api/v1/posts');

        $response->assertFetchedMany($expected);
    }

    public function testPagePagination(): void
    {
        $posts = Post::factory()->count(5)->create();

        $expected = $this->identifiersFor('posts', $posts->take(3));

        $meta = [
            'currentPage' => 1,
            'from' => 1,
            'lastPage' => 2,
            'perPage' => 3,
            'to' => 3,
            'total' => 5,
        ];

        $links = [
            'first' => 'http://localhost/api/v1/posts?' . Arr::query([
                    'page' => ['number' => 1, 'size' => 3],
                    'sort' => '-createdAt',
                ]),
            'next' => 'http://localhost/api/v1/posts?' . Arr::query([
                    'page' => ['number' => 2, 'size' => 3],
                    'sort' => '-createdAt',
                ]),
            'last' => 'http://localhost/api/v1/posts?' . Arr::query([
                    'page' => ['number' => 2, 'size' => 3],
                    'sort' => '-createdAt',
            ]),
        ];

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi()
            ->expects('posts')
            ->page(['number' => 1, 'size' => 3])
            ->get('/api/v1/posts');

        $response
            ->assertFetchedMany($expected)
            ->assertMeta($meta)
            ->assertExactLinks($links);
    }

    public function testMultiPagination(): void
    {
        $posts = Post::factory()->count(6)->create([
            'created_at' => fn() => app(Generator::class)->dateTime(),
        ])->sortByDesc('created_at')->values();

        $expected = $this->identifiersFor('posts', $posts->skip(2)->take(2));

        $meta = [
            'currentPage' => 2,
            'from' => 3,
            'perPage' => 2,
            'to' => 4,
        ];

        $links = [
            'first' => 'http://localhost/api/v1/posts?' . Arr::query([
                    'page' => ['current-page' => 1, 'per-page' => 2],
                    'sort' => '-createdAt',
                ]),
            'next' => 'http://localhost/api/v1/posts?' . Arr::query([
                    'page' => ['current-page' => 3, 'per-page' => 2],
                    'sort' => '-createdAt',
                ]),
            'prev' => 'http://localhost/api/v1/posts?' . Arr::query([
                    'page' => ['current-page' => 1, 'per-page' => 2],
                    'sort' => '-createdAt',
                ]),
        ];

        $response = $this
            ->jsonApi()
            ->expects('posts')
            ->page(['current-page' => 2, 'per-page' => 2])
            ->get('/api/v1/posts');

        $response
            ->assertFetchedMany($expected)
            ->assertMeta($meta)
            ->assertExactLinks($links);
    }

    public function testIncludeAuthor(): void
    {
        $posts = Post::factory()->count(2)->create();

        $expected1 = $this->serializer
            ->post($posts[0])
            ->putRelation('author', $user1 = ['type' => 'users', 'id' => $posts[0]->author]);

        $expected2 = $this->serializer
            ->post($posts[1])
            ->putRelation('author', $user2 = ['type' => 'users', 'id' => $posts[1]->author]);

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

    public function testIdFilter(): void
    {
        $posts = Post::factory()->count(4)->create();
        $expected = $posts->take(2);

        $ids = $expected
            ->map(fn (Post $post) => $post->getRouteKey())
            ->all();

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi()
            ->expects('posts')
            ->filter(['id' => $ids])
            ->get('/api/v1/posts');

        $response->assertFetchedMany(
            $this->identifiersFor('posts', $expected)
        );
    }

    public function testSlugFilter(): void
    {
        $posts = Post::factory()->count(2)->create();
        $expected = $this->serializer->post($posts[1]);

        $response = $this
            ->withoutExceptionHandling()
            ->jsonApi()
            ->expects('posts')
            ->filter(['slug' => $posts[1]->slug])
            ->get('/api/v1/posts');

        $response->assertFetchedOneExact($expected);
    }

    public function testFilteredAndPaginated(): void
    {
        $published = Post::factory()->count(5)->create(['published_at' => now()]);
        Post::factory()->count(2)->create(['published_at' => null]);

        $expected = $this->identifiersFor('posts', $published);

        $meta = [
            'currentPage' => 1,
            'from' => 1,
            'lastPage' => 1,
            'perPage' => 10,
            'to' => 5,
            'total' => 5,
        ];

        $links = [
            'first' => 'http://localhost/api/v1/posts?' . Arr::query([
                    'filter' => ['published' => 'true'],
                    'page' => ['number' => 1, 'size' => 10],
                    'sort' => '-createdAt',
                ]),
            'last' => 'http://localhost/api/v1/posts?' . Arr::query([
                    'filter' => ['published' => 'true'],
                    'page' => ['number' => 1, 'size' => 10],
                    'sort' => '-createdAt',
                ]),
        ];

        $response = $this
            ->jsonApi()
            ->expects('posts')
            ->filter(['published' => 'true'])
            ->page(['number' => 1, 'size' => 10])
            ->get('/api/v1/posts');

        $response
            ->assertFetchedMany($expected)
            ->assertMeta($meta)
            ->assertLinks($links);
    }

    public function testSparseFieldSets(): void
    {
        $posts = Post::factory()->count(3)->create();

        $expected = $posts->map(
            fn(Post $post) => $this->serializer
                ->post($post)
                ->only('author', 'slug', 'synopsis', 'title')
                ->replace('author', ['type' => 'users', 'id' => $post->author])
        );

        $authors = $this->identifiersFor(
            'users', $posts->pluck('author')
        );

        $response = $this
            ->jsonApi('posts')
            ->sparseFields('posts', ['author', 'slug', 'synopsis', 'title'])
            ->sparseFields('users', ['name'])
            ->includePaths('author')
            ->get('/api/v1/posts');

        $response
            ->assertFetchedManyExact($expected)
            ->assertIncluded($authors);
    }

    public function testSparseFieldSetsAndPaginated(): void
    {
        $posts = Post::factory()->count(5)->create();

        $expected = $posts->take(3)->map(
            fn(Post $post) => $this->serializer
                ->post($post)
                ->only('author', 'slug', 'synopsis', 'title')
                ->replace('author', ['type' => 'users', 'id' => $post->author])
        );

        $meta = [
            'currentPage' => 1,
            'from' => 1,
            'lastPage' => 2,
            'perPage' => 3,
            'to' => 3,
            'total' => 5,
        ];

        $links = [
            'first' => 'http://localhost/api/v1/posts?' . Arr::query([
                    'fields' => $fields = [
                        'posts' => 'author,slug,synopsis,title',
                    ],
                    'include' => 'author',
                    'page' => ['number' => 1, 'size' => 3],
                    'sort' => '-createdAt',
                ]),
            'last' => $last = 'http://localhost/api/v1/posts?' . Arr::query([
                    'fields' => $fields,
                    'include' => 'author',
                    'page' => ['number' => 2, 'size' => 3],
                    'sort' => '-createdAt',
                ]),
            'next' => $last,
        ];

        $response = $this
            ->jsonApi('posts')
            ->sparseFields('posts', ['author', 'slug', 'synopsis', 'title'])
            ->includePaths('author')
            ->page(['number' => 1, 'size' => 3])
            ->get('/api/v1/posts');

        $response
            ->assertFetchedManyExact($expected)
            ->assertExactMeta($meta)
            ->assertLinks($links);
    }

    public function testWithCount(): void
    {
        $posts = Post::factory()
            ->has(Tag::factory()->count(1))
            ->count(3)
            ->create();

        $expected = collect($posts)->map(fn($post) => $this->serializer
            ->post($post)
            ->withRelationshipMeta('tags', ['count' => 1])
        )->all();

        /** Draft post should not appear. */
        Post::factory()->create(['published_at' => null]);

        $response = $this
            ->jsonApi()
            ->expects('posts')
            ->query(['withCount' => 'tags'])
            ->get('/api/v1/posts');

        $response->assertFetchedManyExact($expected);
    }

    /**
     * @param string $mediaType
     * @return void
     * @dataProvider notAcceptableMediaTypeProvider
     */
    public function testNotAcceptableMediaType(string $mediaType): void
    {
        $this
            ->jsonApi()
            ->accept($mediaType)
            ->get('/api/v1/posts')
            ->assertStatus(406);
    }
}
