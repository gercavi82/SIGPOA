<?php

namespace App\Console\Commands;

use App\Services\Migration\ReconciliationService;
use Illuminate\Console\Command;

class ReconcileLegacyDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sigpoa:reconcile-legacy {--year= : Año fiscal a auditar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera el informe formal de reconciliación DB2 vs PostgreSQL sin alterar datos';

    /**
     * Execute the console command.
     */
    public function handle(ReconciliationService $reconciliationService): int
    {
        $year = (int) ($this->option('year') ?: date('Y'));

        $this->info("Generando reporte de reconciliación para el ejercicio fiscal {$year}...");

        $report = $reconciliationService->generateReport($year);

        $this->info("\n=== REPORTE FORMAL DE RECONCILIACIÓN DB2 -> POSTGRESQL ===");
        $this->info("Generado el: {$report['generated_at']}");
        $this->info("Integrity SHA-256: {$report['integrity_hash']}");
        $this->info("Estado General: {$report['overall_status']}");

        $rows = [];
        foreach ($report['tables'] as $t) {
            $rows[] = [
                $t['legacy_table'],
                $t['relationship_type'],
                $t['legacy_count'],
                $t['pg_count'],
                $t['count_diff'],
                '$'.number_format($t['legacy_sum'], 2),
                '$'.number_format($t['pg_sum'], 2),
                '$'.number_format($t['sum_diff'], 2),
                $t['status'],
            ];
        }

        $this->table(['Tabla Legacy', 'Tipo Relación', 'Cant DB2', 'Cant PG', 'Dif Cant', 'Suma DB2', 'Suma PG', 'Dif Suma', 'Estado'], $rows);

        return self::SUCCESS;
    }
}
