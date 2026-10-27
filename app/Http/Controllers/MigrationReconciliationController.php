<?php

namespace App\Http\Controllers;

use App\Services\Migration\MigrationService;
use App\Services\Migration\ReconciliationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MigrationReconciliationController extends Controller
{
    public function __construct(
        private readonly ReconciliationService $reconciliationService,
        private readonly MigrationService $migrationService
    ) {}

    public function index(Request $request): View
    {
        $year = (int) $request->query('year', date('Y'));
        $report = $this->reconciliationService->generateReport($year);

        return view('migration.reconciliation', [
            'report' => $report,
            'selectedYear' => $year,
        ]);
    }

    public function execute(Request $request): RedirectResponse
    {
        $year = (int) $request->input('year', date('Y'));
        $dryRun = (bool) $request->input('dry_run', false);

        $result = $this->migrationService->migrateYear($year, $request->user(), $dryRun);

        $msg = $dryRun
            ? "Simulación (Dry-Run) completada: {$result['migrated_activities']} actividades, {$result['migrated_ceilings']} techos, {$result['migrated_certifications']} certif. POA, {$result['migrated_budget_certifications']} certif. presupuestarias, {$result['migrated_modifications']} modificaciones, {$result['migrated_accruals']} devengados."
            : "Migración completada exitosamente para el año {$year}. Reporte de reconciliación generado (SHA-256: {$result['reconciliation_report']['integrity_hash']}).";

        return redirect()->route('migration.reconciliation', ['year' => $year])
            ->with('status', $msg);
    }
}
