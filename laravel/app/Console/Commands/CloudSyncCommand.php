<?php

namespace App\Console\Commands;

use App\Services\CloudSyncService;
use Illuminate\Console\Command;

class CloudSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cloud:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza o catalogo e dados com a VPS Hostinger (akylanches.freedevs.tech)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Conectando a nuvem AKY Lanches (Hostinger)...');
        $cloudUrl = config('services.cloud.url', 'https://akylanches.freedevs.tech');
        $this->line("Endpoint: <comment>{$cloudUrl}</comment>");

        $stats = CloudSyncService::syncCatalogFromCloud();

        if ($stats['success']) {
            $this->info("Sucesso! {$stats['message']}");
            $this->table(
                ['Recurso', 'Quantidade'],
                [
                    ['Categorias', $stats['categories']],
                    ['Produtos', $stats['products']],
                ]
            );
            return Command::SUCCESS;
        }

        $this->warn("Aviso ao sincronizar: {$stats['message']}");
        return Command::SUCCESS;
    }
}

