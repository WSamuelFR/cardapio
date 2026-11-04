<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SqliteBackupService
{
    /**
     * Tabelas principais a serem espelhadas no backup SQLite
     */
    protected static array $tables = [
        'users',
        'categories',
        'products',
        'orders',
        'order_items',
        'system_settings',
    ];

    /**
     * Verifica se o backup SQLite está habilitado
     */
    public static function isEnabled(): bool
    {
        return config('database.sqlite_backup_enabled', env('SQLITE_BACKUP_ENABLED', true));
    }

    /**
     * Garante que o arquivo SQLite e seu diretório existam
     */
    public static function ensureDatabaseFile(): string
    {
        $path = config('database.connections.sqlite_backup.database', database_path('backup/aky_backup.sqlite'));
        $directory = dirname($path);

        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true, true);
        }

        if (!File::exists($path)) {
            touch($path);
        }

        return $path;
    }

    /**
     * Executa as migrations na conexão sqlite_backup se necessário
     */
    public static function migrateBackupDatabase(): void
    {
        try {
            self::ensureDatabaseFile();
            Artisan::call('migrate', [
                '--database' => 'sqlite_backup',
                '--force' => true,
            ]);
        } catch (\Throwable $e) {
            Log::warning('SqliteBackupService::migrateBackupDatabase error: ' . $e->getMessage());
        }
    }

    /**
     * Espelha um registro específico (Insert/Update)
     */
    public static function mirrorRecord(string $table, array $data): void
    {
        if (!self::isEnabled() || empty($data['id'])) {
            return;
        }

        try {
            self::ensureDatabaseFile();

            // Desativa temporariamente chaves estrangeiras no sqlite
            DB::connection('sqlite_backup')->statement('PRAGMA foreign_keys = OFF');

            DB::connection('sqlite_backup')->table($table)->updateOrInsert(
                ['id' => $data['id']],
                $data
            );
        } catch (\Throwable $e) {
            Log::warning("SqliteBackupService::mirrorRecord failed for table {$table}: " . $e->getMessage());
        }
    }

    /**
     * Remove um registro do backup SQLite
     */
    public static function deleteRecord(string $table, mixed $id): void
    {
        if (!self::isEnabled() || empty($id)) {
            return;
        }

        try {
            self::ensureDatabaseFile();
            DB::connection('sqlite_backup')->table($table)->where('id', $id)->delete();
        } catch (\Throwable $e) {
            Log::warning("SqliteBackupService::deleteRecord failed for table {$table}: " . $e->getMessage());
        }
    }

    /**
     * Executa o espelhamento completo de todas as tabelas do MySQL para o SQLite
     */
    public static function fullSync(): array
    {
        self::ensureDatabaseFile();
        self::migrateBackupDatabase();

        $stats = [];
        $sourceConn = config('database.default', 'mysql');

        DB::connection('sqlite_backup')->statement('PRAGMA foreign_keys = OFF');

        foreach (self::$tables as $table) {
            try {
                if (!Schema::connection($sourceConn)->hasTable($table)) {
                    continue;
                }

                if (!Schema::connection('sqlite_backup')->hasTable($table)) {
                    continue;
                }

                $rows = DB::connection($sourceConn)->table($table)->get();
                $count = 0;

                foreach ($rows as $row) {
                    $rowArray = (array) $row;
                    if (isset($rowArray['id'])) {
                        DB::connection('sqlite_backup')->table($table)->updateOrInsert(
                            ['id' => $rowArray['id']],
                            $rowArray
                        );
                        $count++;
                    }
                }

                $stats[$table] = $count;
            } catch (\Throwable $e) {
                Log::error("SqliteBackupService::fullSync error on table {$table}: " . $e->getMessage());
                $stats[$table] = 'Error: ' . $e->getMessage();
            }
        }

        return $stats;
    }
}

