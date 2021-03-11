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

namespace App\JsonApi\V1;

use App\JsonApi\V1\Posts\PostScope;
use App\Models\Post;
use App\Models\Video;
use Illuminate\Support\Facades\Auth;
use LaravelJsonApi\Core\Server\Server as BaseServer;
use LaravelJsonApi\Laravel\Http\Requests\RequestResolver;

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

        RequestResolver::registerCollectionQuery('media', Media\MediaCollectionQuery::class);
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
            Posts\PostSchema::class,
            Tags\TagSchema::class,
            Users\UserSchema::class,
            Videos\VideoSchema::class,
        ];
    }
}
