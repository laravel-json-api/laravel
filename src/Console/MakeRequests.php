<?php
/*
 * Copyright 2021 Cloud Creativity Limited
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

namespace LaravelJsonApi\Laravel\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MakeRequests extends Command
{

    /**
     * @var string
     */
    protected $name = 'jsonapi:requests';

    /**
     * @var string
     */
    protected $description = 'Create new JSON:API resource and query requests.';

    /**
     * Execute the command.
     *
     * @return int
     */
    public function handle(): int
    {
        $args = collect([
            'name' => $this->argument('name'),
            '--server' => $this->option('server'),
            '--force' => $this->option('force'),
        ])->reject(fn($value) => is_null($value))->all();

        $result = $this->call('jsonapi:request', $args);

        if (0 === $result) {
            $args['--both'] = true;
            $result = $this->call('jsonapi:query', $args);
        }

        return $result;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the JSON:API resource type.'],
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the request classes even if they already exists'],
            ['server', 's', InputOption::VALUE_REQUIRED, 'The JSON:API server the requests exists in.'],
        ];
    }
}
