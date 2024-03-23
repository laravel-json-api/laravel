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

use App\Models\User;

class UserPolicy
{
    /**
     * Determine if the user can view the other user.
     *
     * @param User $user
     * @param User $other
     * @return bool
     */
    public function view(User $user, User $other): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the other user's phone.
     *
     * @param User $user
     * @param User $other
     * @return bool
     */
    public function viewPhone(User $user, User $other): bool
    {
        return $user->is($other);
    }

    /**
     * Determine if the user can update the other user's phone.
     *
     * @param User $user
     * @param User $other
     * @return bool
     */
    public function updatePhone(User $user, User $other): bool
    {
        return $user->is($other);
    }
}
