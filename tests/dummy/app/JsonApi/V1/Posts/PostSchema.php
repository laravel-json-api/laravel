<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace App\JsonApi\V1\Posts;

use App\Models\Post;
use LaravelJsonApi\Core\Schema\Attributes\Model;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\ID;
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
use LaravelJsonApi\Eloquent\Pagination\MultiPagination;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;
use LaravelJsonApi\Eloquent\SoftDeletes;
use LaravelJsonApi\Eloquent\Sorting\SortCountable;

#[Model(Post::class)]
class PostSchema extends Schema
{
    use SoftDeletes;

    /**
     * The maximum depth of include paths.
     *
     * @var int
     */
    protected int $maxDepth = 3;

    /**
     * @var string
     */
    protected $defaultSort = '-createdAt';

    /**
     * @inheritDoc
     */
    public function fields(): array
    {
        return [
            ID::make(),
            BelongsTo::make('author')->type('users')->readOnly(),
            HasMany::make('comments')->canCount()->readOnly(),
            Str::make('content'),
            DateTime::make('createdAt')->sortable()->readOnly(),
            SoftDelete::make('deletedAt')->sortable(),
            MorphToMany::make('media', [
                BelongsToMany::make('images'),
                BelongsToMany::make('videos'),
            ])->canCount(),
            DateTime::make('publishedAt')->sortable(),
            Str::make('slug'),
            Str::make('synopsis'),
            BelongsToMany::make('tags')->canCount()->mustValidate(),
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
    public function pagination(): MultiPagination
    {
        return new MultiPagination(
            PagePagination::make()->withoutNestedMeta(),
            PagePagination::make()
                ->withoutNestedMeta()
                ->withSimplePagination()
                ->withPageKey('current-page')
                ->withPerPageKey('per-page')
        );
    }
}
