<?php
/*
 * Copyright 2024 Cloud Creativity Limited
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
