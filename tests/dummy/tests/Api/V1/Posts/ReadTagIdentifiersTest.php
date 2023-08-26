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
use App\Tests\Api\V1\TestCase;

class ReadTagIdentifiersTest extends TestCase
{

    /**
     * @var Post
     */
    private Post $post;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->post = Post::factory()->create();
    }

    public function test(): void
    {
        $tags = Tag::factory()
            ->count(3)
            ->create();

        $this->post->tags()->attach($tags);

        $expected = $this->identifiersFor('tags', $tags);

        $response = $this
            ->jsonApi('tags')
            ->get(url('/api/v1/posts', [$this->post, 'relationships', 'tags']));

        $response
            ->assertFetchedToMany($expected)
//            ->assertExactMeta(['count' => 3]) @TODO
        ;

        $this->markTestIncomplete('@TODO investigate why countable implementation is not working.');
    }

    public function testSort(): void
    {
        $tags = Tag::factory()
            ->count(3)
            ->create();

        $this->post->tags()->attach($tags);

        $expected = $this->identifiersFor('tags', $tags->sortByDesc('name'));

        $response = $this
            ->jsonApi('tags')
            ->sort('-name')
            ->get(url('/api/v1/posts', [$this->post, 'relationships', 'tags']));

        $response->assertFetchedToManyInOrder($expected);
    }

    /**
     * @param string $mediaType
     * @return void
     * @dataProvider notAcceptableMediaTypeProvider
     */
    public function testNotAcceptableMediaType(string $mediaType): void
    {
        $this->jsonApi()
            ->accept($mediaType)
            ->get(url('/api/v1/posts', [$this->post, 'relationships', 'tags']))
            ->assertStatus(406);
    }
}
