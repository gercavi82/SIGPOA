<?php

namespace App\Http\Controllers;

use App\Actions\Execution\CloseExecutionMonthAction;
use App\Http\Requests\CloseExecutionMonthRequest;
use App\Models\ExecutionClosing;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use InvalidArgumentException;

class ExecutionClosingController extends Controller
{
    public function index(): View
    {
        $closings = ExecutionClosing::query()
            ->with('user')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->paginate(15);

        return view('execution-closings.index', [
            'closings' => $closings,
        ]);
    }

    public function store(CloseExecutionMonthRequest $request, CloseExecutionMonthAction $action): RedirectResponse
    {
        try {
            $closing = $action->execute($request->validated(), $request->user());
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['closing' => $e->getMessage()]);
        }

        return redirect()->route('execution-closings.index')
            ->with('status', "Período {$closing->year}-".str_pad((string) $closing->month, 2, '0', STR_PAD_LEFT).' cerrado exitosamente.');
    }
}
