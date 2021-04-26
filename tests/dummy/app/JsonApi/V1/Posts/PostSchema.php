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

namespace App\JsonApi\V1\Posts;

use App\Models\Post;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\Relations\BelongsTo;
use LaravelJsonApi\Eloquent\Fields\Relations\BelongsToMany;
use LaravelJsonApi\Eloquent\Fields\Relations\HasMany;
use LaravelJsonApi\Eloquent\Fields\Relations\MorphToMany;
use LaravelJsonApi\Eloquent\Fields\SoftDelete;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Filters\OnlyTrashed;
use LaravelJsonApi\Eloquent\Filters\Scope;
use LaravelJsonApi\Eloquent\Filters\Where;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;
use LaravelJsonApi\Eloquent\SoftDeletes;
use LaravelJsonApi\Eloquent\Sorting\SortCountable;
use LaravelJsonApi\HashIds\HashId;

class PostSchema extends Schema
{

    use SoftDeletes;

    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    public static string $model = Post::class;

    /**
     * The maximum depth of include paths.
     *
     * @var int
     */
    protected int $maxDepth = 3;

    /**
     * @inheritDoc
     */
    public function fields(): array
    {
        return [
            HashId::make()->alreadyHashed(),
            BelongsTo::make('author')->type('users')->readOnly(),
            HasMany::make('comments')->readOnly(),
            Str::make('content'),
            DateTime::make('createdAt')->sortable()->readOnly(),
            SoftDelete::make('deletedAt')->sortable(),
            MorphToMany::make('media', [
                BelongsToMany::make('images'),
                BelongsToMany::make('videos'),
            ]),
            DateTime::make('publishedAt')->sortable(),
            Str::make('slug'),
            Str::make('synopsis'),
            BelongsToMany::make('tags')->mustValidate(),
            Str::make('title')->sortable(),
            DateTime::make('updatedAt')->sortable()->readOnly(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function filters(): array
    {
        return [
            WhereIdIn::make($this)->delimiter(','),
            Scope::make('published', 'wherePublished')->asBoolean(),
            Where::make('slug')->singular(),
            OnlyTrashed::make('trashed'),
        ];
    }

    /**
     * @inheritDoc
     */
    public function sortables(): iterable
    {
        return [
            SortCountable::make($this, 'comments'),
        ];
    }

    /**
     * @inheritDoc
     */
    public function pagination(): PagePagination
    {
        return PagePagination::make()->withoutNestedMeta();
    }

}
