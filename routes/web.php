<?php

use App\Http\Controllers\AnalyticsApiController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BudgetAccrualController;
use App\Http\Controllers\BudgetCeilingController;
use App\Http\Controllers\BudgetCertificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExecutionBatchController;
use App\Http\Controllers\ExecutionClosingController;
use App\Http\Controllers\ExecutionReconciliationController;
use App\Http\Controllers\MigrationReconciliationController;
use App\Http\Controllers\PoaActivityController;
use App\Http\Controllers\PoaActivityPhaseAllocationController;
use App\Http\Controllers\PoaCertificationController;
use App\Http\Controllers\PoaModificationRequestController;
use App\Http\Controllers\PoaProcessController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login');
});

Route::middleware(['auth', 'active'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::middleware('permission:admin.parameters')->group(function (): void {
        Route::resource('poa-processes', PoaProcessController::class)->parameters(['poa-processes' => 'poaProcess'])->only(['index', 'create', 'store', 'show']);
        Route::post('poa-processes/{poaProcess}/transition', [PoaProcessController::class, 'transition'])->name('poa-processes.transition');
    });
    Route::middleware('permission:poa.view')->group(function (): void {
        Route::resource('poa-activities', PoaActivityController::class)->parameters(['poa-activities' => 'poaActivity'])->only(['index', 'create', 'store', 'show', 'edit', 'update']);
        Route::post('poa-activities/{poaActivity}/transition', [PoaActivityController::class, 'transition'])->name('poa-activities.transition');
        Route::post('poa-activities/{poaActivity}/phase-allocation', [PoaActivityPhaseAllocationController::class, 'store'])->middleware('permission:poa.edit')->name('poa-activities.phase-allocation.store');
    });
    Route::middleware('permission:poa.approve')->group(function (): void {
        Route::resource('budget-ceilings', BudgetCeilingController::class)->parameters(['budget-ceilings' => 'budgetCeiling'])->only(['index', 'create', 'store', 'show']);
        Route::post('budget-ceilings/{budgetCeiling}/transition', [BudgetCeilingController::class, 'transition'])->name('budget-ceilings.transition');
    });
    Route::middleware('permission:certification.create')->group(function (): void {
        Route::resource('poa-certifications', PoaCertificationController::class)->parameters(['poa-certifications' => 'poaCertification'])->only(['index', 'create', 'store', 'show']);
        Route::post('poa-certifications/{poaCertification}/transition', [PoaCertificationController::class, 'transition'])->name('poa-certifications.transition');
    });
    Route::middleware('permission:modification.review,modification.create,modification.verify_budget')->group(function (): void {
        Route::get('modifications', [PoaModificationRequestController::class, 'index'])->name('modifications.index');
        Route::get('modifications/create', [PoaModificationRequestController::class, 'create'])->middleware('permission:modification.create')->name('modifications.create');
        Route::post('modifications', [PoaModificationRequestController::class, 'store'])->middleware('permission:modification.create')->name('modifications.store');
        Route::get('modifications/{poaModificationRequest}', [PoaModificationRequestController::class, 'show'])->name('modifications.show');
        Route::get('modifications/{poaModificationRequest}/edit', [PoaModificationRequestController::class, 'edit'])->middleware('permission:modification.create')->name('modifications.edit');
        Route::put('modifications/{poaModificationRequest}', [PoaModificationRequestController::class, 'update'])->middleware('permission:modification.create')->name('modifications.update');
        Route::post('modifications/{poaModificationRequest}/transition', [PoaModificationRequestController::class, 'transition'])->middleware('permission:modification.review')->name('modifications.transition');
        Route::post('modifications/{poaModificationRequest}/viability', [PoaModificationRequestController::class, 'recordViability'])->middleware('permission:modification.review')->name('modifications.viability');
        Route::post('modifications/{poaModificationRequest}/esigef', [PoaModificationRequestController::class, 'recordEsigef'])->middleware('permission:modification.verify_budget,modification.review')->name('modifications.esigef');
        Route::post('modifications/{poaModificationRequest}/documents', [PoaModificationRequestController::class, 'attachDocument'])->middleware('permission:modification.create,modification.review')->name('modifications.documents.store');
    });

    // Fase 7: Ejecución Presupuestaria y Devengados
    Route::middleware('permission:certification.create,execution.import,modification.verify_budget')->group(function (): void {
        Route::resource('budget-certifications', BudgetCertificationController::class)->parameters(['budget-certifications' => 'budgetCertification'])->only(['index', 'create', 'store', 'show']);
    });

    Route::middleware('permission:execution.import,execution.validate,execution.process')->group(function (): void {
        Route::resource('execution-batches', ExecutionBatchController::class)->parameters(['execution-batches' => 'executionBatch'])->only(['index', 'create', 'store', 'show']);
        Route::post('execution-batches/{executionBatch}/process', [ExecutionBatchController::class, 'process'])->name('execution-batches.process');
        Route::put('execution-batches/rows/{row}/reconcile', [ExecutionReconciliationController::class, 'update'])->name('execution-reconciliation.update');
        Route::get('budget-accruals', [BudgetAccrualController::class, 'index'])->name('budget-accruals.index');
    });

    Route::middleware('permission:execution.close,execution.import')->group(function (): void {
        Route::get('execution-closings', [ExecutionClosingController::class, 'index'])->name('execution-closings.index');
        Route::post('execution-closings', [ExecutionClosingController::class, 'store'])->middleware('permission:execution.close')->name('execution-closings.store');
    });

    // Fase 8: Reportes y Business Intelligence (BI)
    Route::middleware('permission:reports.view')->group(function (): void {
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/poa-by-unit', [ReportController::class, 'poaByUnit'])->name('reports.poa-by-unit');
        Route::get('reports/execution-comparison', [ReportController::class, 'executionComparison'])->name('reports.execution-comparison');
        Route::get('reports/certifications', [ReportController::class, 'certifications'])->name('reports.certifications');
        Route::get('reports/modifications', [ReportController::class, 'modifications'])->name('reports.modifications');
        Route::get('analytics/dataset', [AnalyticsApiController::class, 'executionDataset'])->name('analytics.dataset');
    });

    // Fase 9: Migración / Convivencia DB2 y Reconciliación
    Route::middleware('permission:admin.parameters')->group(function (): void {
        Route::get('migration/reconciliation', [MigrationReconciliationController::class, 'index'])->name('migration.reconciliation');
        Route::post('migration/reconciliation/execute', [MigrationReconciliationController::class, 'execute'])->name('migration.reconciliation.execute');
    });
});
