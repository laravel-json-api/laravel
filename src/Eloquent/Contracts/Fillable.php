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

namespace LaravelJsonApi\Eloquent\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface Fillable
{

    /**
     * Is the field read-only?
     *
     * @param Request $request
     * @return bool
     */
    public function isReadOnly($request): bool;

    /**
     * Fill the model with the value of the JSON API field.
     *
     * @param Model $model
     * @param mixed $value
     */
    public function fill(Model $model, $value): void;
}
