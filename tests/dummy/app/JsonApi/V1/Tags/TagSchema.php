<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace App\JsonApi\V1\Tags;

use App\Models\Tag;
use LaravelJsonApi\Core\Schema\Attributes\Model;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Fields\Relations\BelongsToMany;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;

#[Model(Tag::class)]
class TagSchema extends Schema
{
    /**
     * @inheritDoc
     */
    public function fields(): array
    {
        return [
            ID::make(),
            DateTime::make('createdAt')->sortable()->readOnly(),
            Str::make('name')->sortable(),
            BelongsToMany::make('posts')
                ->canCount()
                ->cannotEagerLoad()
                ->readOnly(),
            DateTime::make('updatedAt')->sortable()->readOnly(),
            BelongsToMany::make('videos')
                ->canCount()
                ->cannotEagerLoad()
                ->readOnly(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function filters(): array
    {
        return [
            WhereIdIn::make($this)->delimiter(','),
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
