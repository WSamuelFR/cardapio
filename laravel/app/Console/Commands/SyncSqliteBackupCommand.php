<?php

namespace App\Console\Commands;

use App\Services\SqliteBackupService;
use Illuminate\Console\Command;

class SyncSqliteBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:sqlite {--migrate-only : Apenas roda as migrations no banco SQLite de backup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Espelha os dados do banco principal MySQL para o banco de backup SQLite';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Iniciando processo de espelhamento para o SQLite de backup...');

        $filePath = SqliteBackupService::ensureDatabaseFile();
        $this->line("Arquivo SQLite: <comment>{$filePath}</comment>");

        $this->info('Garantindo estrutura de tabelas no SQLite...');
        SqliteBackupService::migrateBackupDatabase();

        if ($this->option('migrate-only')) {
            $this->info('Migrations executadas com sucesso no SQLite!');
            return Command::SUCCESS;
        }

        $this->info('Espelhando dados das tabelas...');
        $stats = SqliteBackupService::fullSync();

        $rows = [];
        foreach ($stats as $table => $count) {
            $rows[] = [$table, is_numeric($count) ? "{$count} registros" : $count];
        }

        $this->table(['Tabela', 'Status / Quantidade'], $rows);
        $this->info('Espelhamento SQLite concluído com sucesso!');

        return Command::SUCCESS;
    }
}

