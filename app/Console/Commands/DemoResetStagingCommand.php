<?php

namespace App\Console\Commands;

use Database\Seeders\StagingDemoSeeder;
use Illuminate\Console\Command;

class DemoResetStagingCommand extends Command
{
    protected $signature = 'demo:reset-staging {--force : Run without interactive confirmation}';

    protected $description = 'Reset local/testing/staging data and seed a deterministic demo QA dataset.';

    public function handle(): int
    {
        if (! $this->environmentAllowsReset()) {
            $this->error('Refusing to reset demo data outside local/testing/staging or when APP_MODE=live.');
            $this->line('Current APP_ENV: '.app()->environment());
            $this->line('Current APP_MODE: '.config('app.mode'));

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('This will run migrate:fresh and replace the current local/staging database. Continue?')) {
            $this->warn('Demo staging reset cancelled.');

            return self::FAILURE;
        }

        $this->info('Resetting database with base seed data...');
        $this->call('migrate:fresh', [
            '--seed' => true,
            '--force' => true,
        ]);

        $this->info('Seeding staging demo QA data...');
        $this->call('db:seed', [
            '--class' => StagingDemoSeeder::class,
            '--force' => true,
        ]);

        $this->components->info('Staging demo QA data is ready.');
        $this->line('Demo users: admin@prode.test, mariano@prode.test, ana@prode.test, juan@prode.test, lucia@prode.test, diego@prode.test, sofia@prode.test');
        $this->line('Demo password: password');

        return self::SUCCESS;
    }

    private function environmentAllowsReset(): bool
    {
        if (config('app.mode') === 'live') {
            return false;
        }

        return app()->environment(['local', 'testing', 'staging']);
    }
}
