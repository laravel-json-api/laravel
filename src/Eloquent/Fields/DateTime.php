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

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use function config;

class DateTime extends Attribute
{

    /**
     * Should dates be converted to the defined time zone?
     *
     * @var bool
     */
    private $useTz = true;

    /**
     * @var string|null
     */
    private $tz;

    /**
     * Use the provided timezone.
     *
     * @param string $tz
     * @return $this
     */
    public function useTimezone(string $tz): self
    {
        $this->tz = $tz;
        $this->useTz = true;

        return $this;
    }

    /**
     * Retain the timezone provided in the JSON value.
     *
     * @return $this
     */
    public function retainTimezone(): self
    {
        $this->useTz = false;

        return $this;
    }

    /**
     * Get the server-side timezone.
     *
     * @return string
     */
    public function timezone(): string
    {
        if ($this->tz) {
            return $this->tz;
        }

        return $this->tz = config('app.timezone');
    }

    /**
     * @inheritDoc
     */
    protected function deserialize(Model $model, $value)
    {
        $value = parent::deserialize($model, $value);

        if ($value && true === $this->useTz) {
            return Carbon::parse($value)->setTimezone($this->timezone());
        }

        return $value;
    }
}
