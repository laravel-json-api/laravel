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
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
            Str::make('content')->rules('required'),
            DateTime::make('createdAt')->sortable()->readOnly(),
            SoftDelete::make('deletedAt')->sortable(),
            MorphToMany::make('media', [
                BelongsToMany::make('images'),
                BelongsToMany::make('videos'),
            ])->canCount(),
            DateTime::make('publishedAt')->sortable(),
            Str::make('slug')
                ->rules('required')
                ->creationRules(Rule::unique('posts'))
                ->updateRules(fn($r, Post $model) => Rule::unique('posts')->ignore($model)),
            Str::make('synopsis')->rules('required'),
            BelongsToMany::make('tags')->canCount()->mustValidate(),
            Str::make('title')->sortable()->rules('required'),
            DateTime::make('updatedAt')->sortable()->readOnly(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function filters(): array
    {
        return [
            WhereIdIn::make($this)->delimiter(',')->onlyToMany(),
            Scope::make('published', 'wherePublished')->asBoolean(),
            Where::make('slug')->singular()->rules('string'),
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
        // TODO add validation to the multi-paginator.
//        return new MultiPagination(
//            PagePagination::make()->withoutNestedMeta(),
//            PagePagination::make()
//                ->withoutNestedMeta()
//                ->withSimplePagination()
//                ->withPageKey('current-page')
//                ->withPerPageKey('per-page')
//        );

        return PagePagination::make()
            ->withoutNestedMeta()
            ->withMaxPerPage(200);
    }

    /**
     * @return array
     */
    public function deletionRules(): array
    {
        return [
            'meta.no_comments' => 'accepted',
        ];
    }

    /**
     * @return array
     */
    public function deletionMessages(): array
    {
        return [
            'meta.no_comments.accepted' => 'Cannot delete a post with comments.',
        ];
    }

    /**
     * @param Request|null $request
     * @param Post $post
     * @return array
     */
    public function metaForDeletion(?Request $request, Post $post): array
    {
        return [
            'no_comments' => $post->comments()->doesntExist(),
        ];
    }
}
