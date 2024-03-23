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

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use LaravelJsonApi\Core\Store\LazyRelation;

class PostPolicy
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
     * @param Post $post
     * @return bool
     */
    public function view(?User $user, Post $post): bool
    {
        if ($post->published_at) {
            return true;
        }

        return $this->author($user, $post);
    }

    /**
     * @param User|null $user
     * @param Post $post
     * @return bool
     */
    public function viewAuthor(?User $user, Post $post): bool
    {
        return $this->view($user, $post);
    }

    /**
     * @param User|null $user
     * @param Post $post
     * @return bool
     */
    public function viewComments(?User $user, Post $post): bool
    {
        return $this->view($user, $post);
    }

    /**
     * @param User|null $user
     * @param Post $post
     * @return bool
     */
    public function viewMedia(?User $user, Post $post): bool
    {
        return $this->view($user, $post);
    }

    /**
     * @param User|null $user
     * @param Post $post
     * @return bool
     */
    public function viewTags(?User $user, Post $post): bool
    {
        return $this->view($user, $post);
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
     * @param Post $post
     * @return bool
     */
    public function update(?User $user, Post $post): bool
    {
        return $this->author($user, $post);
    }

    /**
     * @param User|null $user
     * @param Post $post
     * @param LazyRelation $tags
     * @return bool
     */
    public function updateMedia(?User $user, Post $post, LazyRelation $tags): bool
    {
        return $this->author($user, $post);
    }

    /**
     * @param User|null $user
     * @param Post $post
     * @param LazyRelation $tags
     * @return bool
     */
    public function updateTags(?User $user, Post $post, LazyRelation $tags): bool
    {
        $tags->collect()->each(fn(Tag $tag) => $tag);

        return $this->author($user, $post);
    }

    /**
     * @param User|null $user
     * @param Post $post
     * @param LazyRelation $tags
     * @return bool
     */
    public function attachMedia(?User $user, Post $post, LazyRelation $tags): bool
    {
        return $this->author($user, $post);
    }

    /**
     * @param User|null $user
     * @param Post $post
     * @param LazyRelation $tags
     * @return bool
     */
    public function attachTags(?User $user, Post $post, LazyRelation $tags): bool
    {
        return $this->updateTags($user, $post, $tags);
    }

    /**
     * @param User|null $user
     * @param Post $post
     * @param LazyRelation $tags
     * @return bool
     */
    public function detachMedia(?User $user, Post $post, LazyRelation $tags): bool
    {
        return $this->author($user, $post);
    }

    /**
     * @param User|null $user
     * @param Post $post
     * @param LazyRelation $tags
     * @return bool
     */
    public function detachTags(?User $user, Post $post, LazyRelation $tags): bool
    {
        return $this->updateTags($user, $post, $tags);
    }

    /**
     * @param User|null $user
     * @param Post $post
     * @return bool
     */
    public function delete(?User $user, Post $post): bool
    {
        return $this->author($user, $post);
    }

    /**
     * @param User|null $user
     * @return bool
     */
    public function deleteAll(?User $user): bool
    {
        return $user && $user->isAdmin();
    }

    /**
     * @param User|null $user
     * @param Post $post
     * @return bool
     */
    public function author(?User $user, Post $post): bool
    {
        return $user && $post->author->is($user);
    }
}
