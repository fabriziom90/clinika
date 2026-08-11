<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MigrateCore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:core';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Esegue la migration del database core';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->call('migrate', [
            '--path' => 'database/migrations/core',
            '--database' => 'central',
        ]);

        return self::SUCCESS;
    }
}
