<?php
/*
 * Copyright 2022 Cloud Creativity Limited
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

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var string[]
     */
    protected $fillable = [
        'content',
        'published_at',
        'slug',
        'synopsis',
        'title',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * @return BelongsTo
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @return BelongsToMany
     */
    public function images(): BelongsToMany
    {
        return $this->belongsToMany(Image::class);
    }

    /**
     * @return MorphToMany
     */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    /**
     * @return BelongsToMany
     */
    public function videos(): BelongsToMany
    {
        return $this->belongsToMany(Video::class);
    }

    /**
     * @param Builder $query
     * @param bool $published
     * @return Builder
     */
    public function scopeWherePublished(Builder $query, bool $published): Builder
    {
        $column = $this->qualifyColumn('published_at');

        if ($published) {
            return $query->whereNotNull($column);
        }

        return $query->whereNull($column);
    }
}
