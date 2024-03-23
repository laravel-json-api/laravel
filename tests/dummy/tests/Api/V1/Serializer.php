<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
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
                'createdAt' => $image->created_at,
                'updatedAt' => $image->updated_at,
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
            'id' => (string) $id,
            'attributes' => [
                'content' => $post->content,
                'createdAt' => $post->created_at,
                'deletedAt' => $post->deleted_at,
                'publishedAt' => $post->published_at,
                'slug' => $post->slug,
                'synopsis' => $post->synopsis,
                'title' => $post->title,
                'updatedAt' => $post->updated_at,
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
            'id' => (string) $id,
            'attributes' => [
                'createdAt' => $tag->created_at,
                'name' => $tag->name,
                'updatedAt' => $tag->updated_at,
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
            'id' => (string) $id,
            'attributes' => [
                'createdAt' => $user->created_at,
                'name' => $user->name,
                'updatedAt' => $user->updated_at,
            ],
            'relationships' => [
                'phone' => [
                    'links' => [
                        'self' => "{$self}/relationships/phone",
                        'related' => "{$self}/phone",
                    ],
                ],
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
                'createdAt' => $video->created_at,
                'title' => $video->title,
                'updatedAt' => $video->updated_at,
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
