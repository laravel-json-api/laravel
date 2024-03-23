<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace App\JsonApi\V1;

use App\JsonApi\V1\Posts\PostScope;
use App\Models\Post;
use App\Models\Video;
use Illuminate\Support\Facades\Auth;
use LaravelJsonApi\Core\Server\Server as BaseServer;
use LaravelJsonApi\Laravel\LaravelJsonApi;

class Server extends BaseServer
{

    /**
     * The base URI namespace for this server.
     *
     * @var string
     */
    protected string $baseUri = '/api/v1';

    /**
     * Bootstrap the server when it is handling an HTTP request.
     *
     * @return void
     */
    public function serving(): void
    {
        Post::addGlobalScope(new PostScope());
        Post::creating(static function (Post $post) {
            $post->author()->associate(Auth::user());
        });

        Video::creating(static function (Video $video) {
            $video->owner()->associate(Auth::user());
        });

        LaravelJsonApi::registerCollectionQuery(Media\MediaCollectionQuery::class, 'media');
    }

    /**
     * Get the server's list of schemas.
     *
     * @return array
     */
    protected function allSchemas(): array
    {
        return [
            Comments\CommentSchema::class,
            Images\ImageSchema::class,
            Phones\PhoneSchema::class,
            Posts\PostSchema::class,
            Tags\TagSchema::class,
            Users\UserSchema::class,
            Videos\VideoSchema::class,
        ];
    }
}
