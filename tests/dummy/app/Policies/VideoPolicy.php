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

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;
use App\Models\Video;
use LaravelJsonApi\Core\Store\LazyRelation;

class VideoPolicy
{

    /**
     * @param User|null $user
     * @return bool
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * @param User|null $user
     * @param Video $video
     * @return bool
     */
    public function view(?User $user, Video $video): bool
    {
        return true;
    }

    /**
     * @param User|null $user
     * @param Video $video
     * @return bool
     */
    public function viewTags(?User $user, Video $video): bool
    {
        return $this->view($user, $video);
    }

    /**
     * @param User|null $user
     * @return bool
     */
    public function create(?User $user): bool
    {
        return !!$user;
    }

    /**
     * @param User|null $user
     * @param Video $video
     * @return bool
     */
    public function update(?User $user, Video $video): bool
    {
        return $this->owner($user, $video);
    }

    /**
     * @param User|null $user
     * @param Video $video
     * @param LazyRelation $tags
     * @return bool
     */
    public function updateTags(?User $user, Video $video, LazyRelation $tags): bool
    {
        $tags->collect()->each(fn(Tag $tag) => $tag);

        return $this->owner($user, $video);
    }

    /**
     * @param User|null $user
     * @param Video $video
     * @param LazyRelation $tags
     * @return bool
     */
    public function attachTags(?User $user, Video $video, LazyRelation $tags): bool
    {
        return $this->updateTags($user, $video, $tags);
    }

    /**
     * @param User|null $user
     * @param Video $video
     * @param LazyRelation $tags
     * @return bool
     */
    public function detachTags(?User $user, Video $video, LazyRelation $tags): bool
    {
        return $this->updateTags($user, $video, $tags);
    }

    /**
     * @param User|null $user
     * @param Video $video
     * @return bool
     */
    public function delete(?User $user, Video $video): bool
    {
        return $this->owner($user, $video);
    }

    /**
     * @param User|null $user
     * @param Video $video
     * @return bool
     */
    public function owner(?User $user, Video $video): bool
    {
        return $user && $video->owner->is($user);
    }
}
