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

namespace App\JsonApi\V1\Comments;

use App\Models\Comment;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Fields\Relations\BelongsTo;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Filters\WhereIn;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;

class CommentSchema extends Schema
{

    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    public static string $model = Comment::class;

    /**
     * @inheritDoc
     */
    public function fields(): array
    {
        return [
            ID::make(),
            Str::make('content'),
            DateTime::make('createdAt')->sortable()->readOnly(),
            BelongsTo::make('post'),
            DateTime::make('updatedAt')->sortable()->readOnly(),
            BelongsTo::make('user')->readOnly(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function filters(): array
    {
        return [
            WhereIn::make('id', $this->idColumn())->delimiter(','),
        ];
    }

    /**
     * @inheritDoc
     */
    public function pagination(): ?Paginator
    {
        return PagePagination::make();
    }

}
