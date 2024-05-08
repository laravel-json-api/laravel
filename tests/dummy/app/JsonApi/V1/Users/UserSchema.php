<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace App\JsonApi\V1\Users;

use App\Models\User;
use LaravelJsonApi\Core\Schema\Attributes\Model;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Fields\Relations\HasOne;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Filters\Where;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;

#[Model(User::class)]
class UserSchema extends Schema
{
    /**
     * @inheritDoc
     */
    public function fields(): array
    {
        return [
            ID::make(),
            DateTime::make('createdAt')->readOnly(),
            Str::make('name'),
            HasOne::make('phone')->deleteDetachedModel(),
            DateTime::make('updatedAt')->readOnly(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function filters(): array
    {
        return [
            WhereIdIn::make($this)->delimiter(',')->onlyToMany(),
            Where::make('email')->singular()->rules('email'),
        ];
    }

    /**
     * @inheritDoc
     */
    public function pagination(): PagePagination
    {
        return PagePagination::make();
    }

}
