<?php

namespace App\Http\Controllers;

use App\Actions\Execution\ImportExecutionCsvAction;
use App\Actions\Execution\ProcessExecutionBatchAction;
use App\Http\Requests\UploadExecutionBatchRequest;
use App\Models\BudgetCertification;
use App\Models\ImportBatch;
use App\Models\PoaActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class ExecutionBatchController extends Controller
{
    public function index(): View
    {
        $batches = ImportBatch::query()
            ->with(['importer', 'processor'])
            ->latest('id')
            ->paginate(15);

        return view('execution-batches.index', [
            'batches' => $batches,
        ]);
    }

    public function create(): View
    {
        return view('execution-batches.create');
    }

    public function store(UploadExecutionBatchRequest $request, ImportExecutionCsvAction $action): RedirectResponse
    {
        try {
            $result = $action->execute(
                $request->file('file'),
                $request->integer('year'),
                $request->filled('month') ? $request->integer('month') : null,
                $request->user()
            );
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['file' => $e->getMessage()]);
        }

        return redirect()->route('execution-batches.show', $result['batch'])
            ->with('status', "Lote {$result['batch']->batch_code} importado: {$result['rows_count']} filas leídas ({$result['valid_count']} válidas, {$result['unmatched_count']} por conciliar, {$result['invalid_count']} inválidas).");
    }

    public function show(Request $request, ImportBatch $executionBatch): View
    {
        $statusFilter = $request->query('status');

        $rowsQuery = $executionBatch->rows()->with(['poaActivity', 'budgetCertification'])->orderBy('row_number');

        if ($statusFilter && in_array($statusFilter, ['VALIDO', 'INVALIDO', 'CONCILIADO', 'NO_CONCILIADO', 'PROCESADO'])) {
            $rowsQuery->where('status', $statusFilter);
        }

        $activities = PoaActivity::query()->where('status', 'APROBADA')->orderBy('number')->get();
        $certifications = BudgetCertification::query()->where('year', $executionBatch->year)->orderBy('certification_number')->get();

        return view('execution-batches.show', [
            'batch' => $executionBatch->load(['importer', 'processor']),
            'rows' => $rowsQuery->paginate(25)->withQueryString(),
            'activities' => $activities,
            'certifications' => $certifications,
            'currentFilter' => $statusFilter,
        ]);
    }

    public function process(Request $request, ImportBatch $executionBatch, ProcessExecutionBatchAction $action): RedirectResponse
    {
        if (! $request->user()->can('execution.process')) {
            abort(403, 'No tiene permisos para procesar lotes de ejecución.');
        }

        try {
            $result = $action->execute($executionBatch, $request->user());
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['process' => $e->getMessage()]);
        }

        return back()->with('status', "Lote procesado exitosamente: {$result['processed_count']} devengados generados por un total de $".number_format($result['total_accrued'], 2).'.');
    }
}
