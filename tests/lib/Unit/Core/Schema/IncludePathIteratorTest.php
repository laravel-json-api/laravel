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

namespace LaravelJsonApi\Tests\Unit\Core\Schema;

use LaravelJsonApi\Contracts\Schema\Container;
use LaravelJsonApi\Contracts\Schema\PolymorphicRelation;
use LaravelJsonApi\Contracts\Schema\Relation;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Core\Schema\IncludePathIterator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IncludePathIteratorTest extends TestCase
{

    /**
     * @var Container|MockObject
     */
    private Container $schemas;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->schemas = $this->createMock(Container::class);
        $this->schemas->method('schemaFor')->willReturnMap([
            ['images', $image = $this->createMock(Schema::class)],
            ['posts', $post = $this->createMock(Schema::class)],
            ['users', $user = $this->createMock(Schema::class)],
            ['comments', $comment = $this->createMock(Schema::class)],
            ['user-profiles', $profile = $this->createMock(Schema::class)],
        ]);

        $image->method('relationships')->willReturn([
            $this->createPolymorph('imageable', 'imageables', ['posts', 'users']),
            $this->createIgnoredRelation(),
        ]);

        $post->method('relationships')->willReturn([
            $this->createRelation('author', 'users'),
            $this->createRelation('comments', 'comments'),
            $this->createRelation('image', 'images'),
            $this->createIgnoredRelation(),
        ]);

        $user->method('relationships')->willReturn([
            $this->createRelation('image', 'images'),
            $this->createRelation('posts', 'posts'),
            $this->createRelation('profile', 'user-profiles'),
            $this->createIgnoredRelation(),
        ]);

        $comment->method('relationships')->willReturn([
            $this->createRelation('user', 'users'),
            $this->createIgnoredRelation(),
        ]);

        $profile->method('relationships')->willReturn([$this->createIgnoredRelation()]);
    }

    /**
     * @return array[]
     */
    public function depthProvider(): array
    {
        return [
            'one' => [1, ['author', 'comments', 'image']],
            'two' => [2, [
                'author',
                'author.image',
                'author.posts',
                'author.profile',
                'comments',
                'comments.user',
                'image',
                'image.imageable',
            ]],
            'three' => [3, [
                'author',
                'author.image',
                'author.image.imageable',
                'author.posts',
                'author.posts.author',
                'author.posts.comments',
                'author.posts.image',
                'author.profile',
                'comments',
                'comments.user',
                'comments.user.image',
                'comments.user.posts',
                'comments.user.profile',
                'image',
                'image.imageable', // polymorph at depth, terminate.
            ]],
            'four' => [4, [
                'author',
                'author.image',
                'author.image.imageable', // polymorph at depth, terminate
                'author.posts',
                'author.posts.author',
                'author.posts.author.image',
                'author.posts.author.posts',
                'author.posts.author.profile',
                'author.posts.comments',
                'author.posts.comments.user',
                'author.posts.image',
                'author.posts.image.imageable',
                'author.profile',
                'comments',
                'comments.user',
                'comments.user.image',
                'comments.user.image.imageable',
                'comments.user.posts',
                'comments.user.posts.author',
                'comments.user.posts.comments',
                'comments.user.posts.image',
                'comments.user.profile',
                'image',
                'image.imageable', // polymorph at depth, terminate.
            ]],
        ];
    }

    /**
     * @param int $depth
     * @param array $expected
     * @dataProvider depthProvider
     */
    public function test(int $depth, array $expected): void
    {
        $iterator = new IncludePathIterator(
            $this->schemas,
            $this->schemas->schemaFor('posts'),
            $depth
        );

        $this->assertSame($expected, $iterator->toArray());
    }

    /**
     * @return array[]
     */
    public function polymorphProvider(): array
    {
        return [
            'one' => [1, ['imageable']],
            'two' => [2, [
                'imageable',
                'imageable.author', // post
                'imageable.comments', // post
                'imageable.image', // post + user
                'imageable.posts', // user
                'imageable.profile', // user
            ]],
            'three' => [3, [
                'imageable',
                'imageable.author', // post
                'imageable.author.image',
                'imageable.author.posts',
                'imageable.author.profile',
                'imageable.comments', // post
                'imageable.comments.user',
                'imageable.image', // post + user
                'imageable.image.imageable',
                'imageable.posts', // user
                'imageable.posts.author',
                'imageable.posts.comments',
                'imageable.posts.image',
                'imageable.profile', // user
            ]],
        ];
    }

    /**
     * Test polymorphs.
     *
     * The polymorph is at the start, then we can iterate through the
     * different schemas supported by that relation.
     *
     * @param int $depth
     * @param array $expected
     * @dataProvider polymorphProvider
     */
    public function testPolymorph(int $depth, array $expected): void
    {
        $iterator = new IncludePathIterator(
            $this->schemas,
            $this->schemas->schemaFor('images'),
            $depth
        );

        $this->assertSame($expected, $iterator->toArray());
    }

    /**
     * @param string $name
     * @param string $inverse
     * @return Relation|MockObject
     */
    private function createRelation(string $name, string $inverse): Relation
    {
        $relation = $this->createMock(Relation::class);
        $relation->method('isIncludePath')->willReturn(true);
        $relation->method('name')->willReturn($name);
        $relation->method('inverse')->willReturn($inverse);

        return $relation;
    }

    /**
     * @param string $name
     * @param string $psuedoType
     * @param array $inverse
     * @return Relation
     */
    private function createPolymorph(string $name, string $psuedoType, array $inverse): Relation
    {
        $relation = $this->createMock(PolymorphicRelation::class);
        $relation->method('isIncludePath')->willReturn(true);
        $relation->method('name')->willReturn($name);
        $relation->method('inverse')->willReturn($psuedoType);
        $relation->method('inverseTypes')->willReturn($inverse);

        return $relation;
    }

    /**
     * @return Relation
     */
    private function createIgnoredRelation(): Relation
    {
        $relation = $this->createMock(Relation::class);
        $relation->method('isIncludePath')->willReturn(false);
        $relation->expects($this->never())->method('name');
        $relation->expects($this->never())->method('inverse');

        return $relation;
    }
}
