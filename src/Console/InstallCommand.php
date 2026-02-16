<?php

namespace Emir\Webartisan\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'webartisan:install
                            {--force : Overwrite existing files}';

    /**
     * The console command description.
     */
    protected $description = 'Install the Webartisan package resources';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->components->info('Installing Webartisan...');

        $this->publishConfig();
        $this->publishAssets();

        $this->newLine();
        $this->components->info('Webartisan installed successfully.');
        $this->components->info('Visit /webartisan in your browser to access the terminal.');

        return self::SUCCESS;
    }

    /**
     * Publish the configuration file.
     */
    protected function publishConfig(): void
    {
        $params = [
            '--provider' => 'Emir\Webartisan\WebartisanServiceProvider',
            '--tag' => 'webartisan-config',
        ];

        if ($this->option('force')) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);
    }

    /**
     * Publish the frontend assets.
     */
    protected function publishAssets(): void
    {
        $params = [
            '--provider' => 'Emir\Webartisan\WebartisanServiceProvider',
            '--tag' => 'webartisan-assets',
        ];

        if ($this->option('force')) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);
    }
}
