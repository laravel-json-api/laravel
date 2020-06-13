<?php

declare(strict_types=1);

namespace DummyApp\Tests\Api\V1;

use DummyApp\Post;
use LaravelJsonApi\Core\Document\ResourceObject;

class Serializer
{

    /**
     * Get the expected post resource.
     *
     * @param Post $post
     * @return ResourceObject
     */
    public function post(Post $post): ResourceObject
    {
        $self = "http://localhost/api/v1/posts/{$post->getRouteKey()}";

        return ResourceObject::create([
            'type' => 'posts',
            'id' => (string) $post->getRouteKey(),
            'attributes' => [
                'content' => $post->content,
                'createdAt' => $post->created_at->jsonSerialize(),
                'synopsis' => $post->synopsis,
                'title' => $post->title,
                'updatedAt' => $post->updated_at->jsonSerialize(),
            ],
            'relationships' => [
                'author' => [
                    'links' => [
                        'self' => "{$self}/relationships/author",
                        'related' => "{$self}/author",
                    ],
                ],
            ],
            'links' => [
                'self' => $self,
            ],
        ]);
    }
}
