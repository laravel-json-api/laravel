<?php

namespace LaravelJsonApi\Laravel\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class StubPublish extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jsonapi:stubs {--force : Overwrite any existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish all JSON:API stubs that are available for customization.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        if (! is_dir($stubsPath = $this->laravel->basePath('stubs'))) {
            (new Filesystem)->makeDirectory($stubsPath);
        }

        if (!is_dir($stubsPath = $stubsPath . '/jsonapi')) {
            (new Filesystem())->makeDirectory($stubsPath);
        }

        $files = [
            'controller.stub',
            'filter.stub',
            'query.stub',
            'query-collection.stub',
            'request.stub',
            'resource.stub',
            'schema.stub',
            'server.stub',
        ];

        foreach ($files as $file) {
            $from =  __DIR__ . '/../../stubs/'  . $file;
            $to = $stubsPath . '/' . $file;

            if (! file_exists($to) || $this->option('force')) {
                file_put_contents($to, file_get_contents($from));
            }
        }

        $this->info('JSON:API stubs published successfully.');

        return 0;
    }
}
