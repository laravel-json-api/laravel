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

namespace App\Tests\Api\V1;

use App\Models\Image;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Models\Video;
use LaravelJsonApi\Core\Document\ResourceObject;
use function url;

class Serializer
{

    /**
     * Get the expected resource for an image model.
     *
     * @param Image $image
     * @return ResourceObject
     */
    public function image(Image $image): ResourceObject
    {
        $self = url('/api/v1/images', $image);

        return ResourceObject::fromArray([
            'type' => 'images',
            'id' => (string) $image->getRouteKey(),
            'attributes' => [
                'createdAt' => $image->created_at->jsonSerialize(),
                'updatedAt' => $image->updated_at->jsonSerialize(),
                'url' => $image->url,
            ],
            'links' => [
                'self' => $self,
            ],
        ]);
    }

    /**
     * Get the expected post resource.
     *
     * @param Post $post
     * @return ResourceObject
     */
    public function post(Post $post): ResourceObject
    {
        $self = url(
            '/api/v1/posts',
            $id = $post->getRouteKey(),
        );

        return ResourceObject::fromArray([
            'type' => 'posts',
            'id' => $id,
            'attributes' => [
                'content' => $post->content,
                'createdAt' => optional($post->created_at)->jsonSerialize(),
                'deletedAt' => optional($post->deleted_at)->jsonSerialize(),
                'publishedAt' => optional($post->published_at)->jsonSerialize(),
                'slug' => $post->slug,
                'synopsis' => $post->synopsis,
                'title' => $post->title,
                'updatedAt' => optional($post->updated_at)->jsonSerialize(),
            ],
            'relationships' => [
                'author' => [
                    'links' => [
                        'self' => "{$self}/relationships/author",
                        'related' => "{$self}/author",
                    ],
                ],
                'comments' => [
                    'links' => [
                        'self' => "{$self}/relationships/comments",
                        'related' => "{$self}/comments",
                    ],
                ],
                'media' => [
                    'links' => [
                        'self' => "{$self}/relationships/media",
                        'related' => "{$self}/media",
                    ],
                ],
                'tags' => [
                    'links' => [
                        'self' => "{$self}/relationships/tags",
                        'related' => "{$self}/tags",
                    ],
                ],
            ],
            'links' => [
                'self' => $self,
            ],
        ]);
    }

    /**
     * Get the expected tag resource.
     *
     * @param Tag $tag
     * @return ResourceObject
     */
    public function tag(Tag $tag): ResourceObject
    {
        $self = url(
            '/api/v1/tags',
            $id = $tag->getRouteKey(),
        );

        return ResourceObject::fromArray([
            'type' => 'tags',
            'id' => $id,
            'attributes' => [
                'createdAt' => $tag->created_at->jsonSerialize(),
                'name' => $tag->name,
                'updatedAt' => $tag->updated_at->jsonSerialize(),
            ],
            'relationships' => [
                'posts' => [
                    'links' => [
                        'self' => "{$self}/relationships/posts",
                        'related' => "{$self}/posts",
                    ],
                ],
                'videos' => [
                    'links' => [
                        'self' => "{$self}/relationships/videos",
                        'related' => "{$self}/videos",
                    ],
                ],
            ],
            'links' => [
                'self' => $self,
            ],
        ]);
    }

    /**
     * Get the expected user resource.
     *
     * @param User $user
     * @return ResourceObject
     */
    public function user(User $user): ResourceObject
    {
        $self = url(
            '/api/v1/users',
            $id = $user->getRouteKey()
        );

        return ResourceObject::fromArray([
            'type' => 'users',
            'id' => $id,
            'attributes' => [
                'createdAt' => $user->created_at->jsonSerialize(),
                'name' => $user->name,
                'updatedAt' => $user->updated_at->jsonSerialize(),
            ],
            'links' => [
                'self' => $self,
            ],
        ]);
    }


    /**
     * Get the expected resource for a video model.
     *
     * @param Video $video
     * @return ResourceObject
     */
    public function video(Video $video): ResourceObject
    {
        $self = url('/api/v1/videos', $video);

        return ResourceObject::fromArray([
            'type' => 'videos',
            'id' => (string) $video->getRouteKey(),
            'attributes' => [
                'createdAt' => $video->created_at->jsonSerialize(),
                'title' => $video->title,
                'updatedAt' => $video->updated_at->jsonSerialize(),
                'url' => $video->url,
            ],
            'relationships' => [
                'tags' => [
                    'links' => [
                        'self' => "{$self}/relationships/tags",
                        'related' => "{$self}/tags",
                    ],
                ],
            ],
            'links' => [
                'self' => $self,
            ],
        ]);
    }
}
