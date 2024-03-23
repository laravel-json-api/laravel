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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class PostScope implements Scope
{

    /**
     * @inheritDoc
     */
    public function apply(Builder $builder, Model $model)
    {
        /**
         * If there is no authenticated user, then we just
         * need to ensure only published posts are returned.
         */
        if (Auth::guest()) {
            $builder->whereNotNull(
                $model->qualifyColumn('published_at')
            );
            return;
        }

        /**
         * If there is an authenticated user, then they
         * can see either published posts OR posts
         * where they are the author.
         */
        $builder->where(function ($query) use ($model) {
            return $query
                ->whereNotNull($model->qualifyColumn('published_at'))
                ->orWhere($model->qualifyColumn('author_id'), Auth::id());
        });
    }

}
