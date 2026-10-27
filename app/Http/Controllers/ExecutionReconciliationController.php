<?php

namespace App\Http\Controllers;

use App\Actions\Execution\ReconcileBatchRowAction;
use App\Http\Requests\ReconcileBatchRowRequest;
use App\Models\ImportBatchRow;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;

class ExecutionReconciliationController extends Controller
{
    public function update(ReconcileBatchRowRequest $request, ImportBatchRow $row, ReconcileBatchRowAction $action): RedirectResponse
    {
        try {
            $action->execute($row, $request->validated(), $request->user());
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['reconciliation' => $e->getMessage()]);
        }

        return back()->with('status', "Fila #{$row->row_number} conciliada correctamente.");
    }
}
