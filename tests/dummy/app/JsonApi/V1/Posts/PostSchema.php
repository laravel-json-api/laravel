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

namespace DummyApp\JsonApi\V1\Posts;

use DummyApp\Post;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Pagination\StandardPaginator;
use LaravelJsonApi\Eloquent\Schema;

class PostSchema extends Schema
{

    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    protected $model = Post::class;

    /**
     * The resource the schema corresponds to.
     *
     * @var string
     */
    protected $resource = PostResource::class;

    /**
     * @inheritDoc
     */
    public function fields(): array
    {
        return [
            Str::make('content'),
            DateTime::make('createdAt')->sortable()->readOnly(),
            Str::make('synopsis'),
            Str::make('title'),
            Str::make('updatedAt')->readOnly(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function filters(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function pagination(): ?Paginator
    {
        return new StandardPaginator();
    }

}
