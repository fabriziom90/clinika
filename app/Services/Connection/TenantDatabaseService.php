<?php

namespace App\Services\Connection;

use App\Models\Clinic;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantDatabaseService
{
    public function connect(Clinic $clinic): void
    {
        Config::set('database.connections.tenant', [
            'driver' => 'mysql',
            'host' => $clinic->db_host,
            'port' => $clinic->db_port,
            'database' => $clinic->database,
            'username' => $clinic->db_username,
            'password' => $clinic->db_password,
            'unix_socket' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ]);

        DB::purge('tenant');

        Config::set('database.default', 'tenant');

    }

    public function createDatabase(Clinic $clinic): void
    {
        $database = str_replace('`', '``', $clinic->database);

        DB::connection('central')->statement(
            "CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        );
    }
}
