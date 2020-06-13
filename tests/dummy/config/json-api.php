<?php

return [
    'servers' => [
        'v1' => [
            'resources' => [
                \DummyApp\Post::class => \DummyApp\JsonApi\V1\Posts\PostResource::class,
                \DummyApp\User::class => \DummyApp\JsonApi\V1\Users\UserResource::class,
            ],
        ],
    ],
];
