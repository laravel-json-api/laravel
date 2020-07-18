<?php
/**
 * Copyright 2020 Cloud Creativity Limited
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

namespace LaravelJsonApi\Eloquent\Fields;

use Closure;
use Illuminate\Http\Request;

trait Fillable
{

    /**
     * Whether the field is read-only.
     *
     * @var Closure|bool
     */
    private $readOnly = false;

    /**
     * Mark the field as read-only.
     *
     * @param Closure|null $callback
     * @return $this
     */
    public function readOnly(Closure $callback = null): self
    {
        $this->readOnly = $callback ?: true;

        return $this;
    }

    /**
     * Mark the field as read only when the resource is being created.
     *
     * @return $this
     */
    public function readOnlyOnCreate(): self
    {
        $this->readOnly(static function ($request) {
            return $request->isMethod('POST');
        });

        return $this;
    }

    /**
     * Mark the field as read only when the resource is being updated.
     *
     * @return $this
     */
    public function readOnlyOnUpdate(): self
    {
        $this->readOnly(static function ($request) {
            return $request->isMethod('PATCH');
        });

        return $this;
    }

    /**
     * Is the field read-only?
     *
     * @param Request $request
     * @return bool
     */
    public function isReadOnly($request): bool
    {
        if ($this->readOnly instanceof Closure) {
            return true === ($this->readOnly)($request);
        }

        return true === $this->readOnly;
    }

}
