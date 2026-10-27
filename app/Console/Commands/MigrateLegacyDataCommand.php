<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Migration\MigrationService;
use Illuminate\Console\Command;

class MigrateLegacyDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sigpoa:migrate-legacy {--year= : Año fiscal a migrar} {--dry-run : Ejecutar validación sin persistir en BD}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migra datos desde IBM i / DB2 hacia PostgreSQL con validación y reporte de reconciliación';

    /**
     * Execute the console command.
     */
    public function handle(MigrationService $migrationService): int
    {
        $year = (int) ($this->option('year') ?: date('Y'));
        $dryRun = (bool) $this->option('dry-run');

        $this->info("Iniciando proceso de migración para el ejercicio fiscal {$year}".($dryRun ? ' (MODO SIMULACIÓN / DRY-RUN)' : '').'...');

        $user = User::query()->first() ?? User::factory()->create(['name' => 'Sistema Migración']);

        $result = $migrationService->migrateYear($year, $user, $dryRun);

        $this->table(
            ['Entidad Migrada', 'Total Registros'],
            [
                ['Actividades POA (PLAARC/PLAAR1)', $result['migrated_activities']],
                ['Techos Presupuestarios (TECARC/TECAR1)', $result['migrated_ceilings']],
                ['Certificaciones POA (CERAR2/LINARC)', $result['migrated_certifications']],
                ['Certificaciones Presupuestarias (CERA01/LINAR6)', $result['migrated_budget_certifications']],
                ['Modificaciones POA (SOLAR1/LINAR2)', $result['migrated_modifications']],
                ['Devengados / CURs (LINAR1)', $result['migrated_accruals']],
            ]
        );

        $reconciliation = $result['reconciliation_report'];
        $this->info("\n=== REPORTE FORMAL DE RECONCILIACIÓN (SHA-256: {$reconciliation['integrity_hash']}) ===");
        $this->info("Estado General: {$reconciliation['overall_status']}");

        $rows = [];
        foreach ($reconciliation['tables'] as $t) {
            $rows[] = [
                $t['legacy_table'],
                $t['relationship_type'],
                $t['legacy_count'],
                $t['pg_count'],
                '$'.number_format($t['legacy_sum'], 2),
                '$'.number_format($t['pg_sum'], 2),
                $t['status'],
            ];
        }

        $this->table(['Tabla Legacy', 'Tipo Relación', 'Cant. DB2', 'Cant. PG', 'Monto DB2', 'Monto PG', 'Estado'], $rows);

        return self::SUCCESS;
    }
}
