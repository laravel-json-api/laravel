<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
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
