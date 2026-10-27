<x-layouts.app title="Devengados y Seguimiento CUR">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Devengados / CURs (LINAR1)</h1>
                <p class="text-sm text-slate-500">Consulta consolidada de devengados presupuestarios e impacto de ejecución en el POA.</p>
            </div>
            @can('execution.import')
                <a href="{{ route('execution-batches.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-institutional-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-institutional-800 focus:outline-none focus:ring-2 focus:ring-institutional-600">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                    </svg>
                    Importar Devengados CSV
                </a>
            @endcan
        </div>

        <!-- Filtros y Búsqueda -->
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('budget-accruals.index') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5 items-end">
                <div>
                    <label for="filter_year" class="block text-xs font-semibold uppercase text-slate-600">Año *</label>
                    <input type="number" name="year" id="filter_year" value="{{ $selectedYear }}" class="mt-1 w-full rounded-lg border border-slate-300 p-2 text-xs focus:border-institutional-600 focus:outline-none">
                </div>

                <div>
                    <label for="filter_month" class="block text-xs font-semibold uppercase text-slate-600">Mes</label>
                    <select name="month" id="filter_month" class="mt-1 w-full rounded-lg border border-slate-300 p-2 text-xs focus:border-institutional-600 focus:outline-none">
                        <option value="">-- Todos los meses --</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                Mes {{ $m }} - {{ ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'][$m - 1] }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label for="filter_cur" class="block text-xs font-semibold uppercase text-slate-600">Nro. CUR</label>
                    <input type="text" name="cur" id="filter_cur" value="{{ $selectedCur }}" placeholder="CUR..." class="mt-1 w-full rounded-lg border border-slate-300 p-2 text-xs focus:border-institutional-600 focus:outline-none">
                </div>

                <div>
                    <label for="filter_activity" class="block text-xs font-semibold uppercase text-slate-600">Actividad POA</label>
                    <select name="activity_id" id="filter_activity" class="mt-1 w-full rounded-lg border border-slate-300 p-2 text-xs focus:border-institutional-600 focus:outline-none">
                        <option value="">-- Todas las actividades --</option>
                        @foreach($activities as $act)
                            <option value="{{ $act->id }}" {{ $selectedActivityId == $act->id ? 'selected' : '' }}>
                                {{ $act->code ?? $act->number }} - {{ Str::limit($act->name ?? $act->description, 25) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="w-full rounded-lg bg-slate-800 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-900">
                        Filtrar
                    </button>
                    <a href="{{ route('budget-accruals.index') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        Limpiar
                    </a>
                </div>
            </form>
        </div>

        <!-- Ficha de Total Devengado Filtrado -->
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase text-slate-500">Monto Devengado Total (Filtro Actual)</span>
                <p class="mt-1 text-2xl font-bold font-mono text-emerald-700">${{ number_format($totalAccrued, 2) }}</p>
            </div>
            <div class="text-right text-xs text-slate-400">
                Registros encontrados: <strong class="text-slate-800">{{ $accruals->total() }}</strong>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase text-slate-500">
                        <tr>
                            <th class="px-6 py-3.5">Nro. CUR / Línea</th>
                            <th class="px-6 py-3.5">Fecha</th>
                            <th class="px-6 py-3.5">Período</th>
                            <th class="px-6 py-3.5">Actividad POA</th>
                            <th class="px-6 py-3.5">Certif. Presupuestaria</th>
                            <th class="px-6 py-3.5">Lote Origen</th>
                            <th class="px-6 py-3.5 text-right">Monto Devengado ($)</th>
                            <th class="px-6 py-3.5">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($accruals as $acc)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-6 py-4 font-mono font-bold text-slate-900">
                                    {{ $acc->cur_number }} <span class="text-slate-400 font-normal">#{{ $acc->cur_line }}</span>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-600">
                                    {{ $acc->accrual_date?->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 text-xs font-semibold text-slate-700">
                                    {{ $acc->month }}/{{ $acc->year }}
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    <span class="font-semibold text-slate-900">{{ $acc->activity?->code ?? $acc->activity?->number }}</span>
                                    <span class="block text-[11px] text-slate-500">{{ Str::limit($acc->activity?->name ?? $acc->activity?->description, 35) }}</span>
                                </td>
                                <td class="px-6 py-4 text-xs font-mono">
                                    @if($acc->budgetCertification)
                                        <a href="{{ route('budget-certifications.show', $acc->budgetCertification) }}" class="text-institutional-700 hover:underline">
                                            {{ $acc->budgetCertification->certification_number }}
                                        </a>
                                    @else
                                        <span class="text-slate-400 italic">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs font-mono">
                                    @if($acc->batch)
                                        <a href="{{ route('execution-batches.show', $acc->batch) }}" class="text-slate-600 hover:underline">
                                            {{ $acc->batch->batch_code }}
                                        </a>
                                    @else
                                        <span class="text-slate-400 italic">Manual</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-mono font-bold text-slate-900">
                                    ${{ number_format($acc->accrued_amount, 2) }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">
                                        {{ $acc->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-sm text-slate-500">
                                    No se encontraron devengados registrados con los filtros aplicados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($accruals->hasPages())
                <div class="border-t border-slate-200 bg-slate-50 px-6 py-3">
                    {{ $accruals->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
