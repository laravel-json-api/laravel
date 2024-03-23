<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
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
