<x-layouts.app title="Cierres Mensuales de Ejecución">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Cierres Mensuales de Ejecución</h1>
                <p class="text-sm text-slate-500">Bloqueo y consolidación mensual de devengados presupuestarios y metas POA.</p>
            </div>
        </div>

        @if(session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                <p class="font-semibold">Atención:</p>
                <ul class="mt-1 list-disc pl-5 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Formulario de Cierre de Mes (DPGE / Financiero / Admin) -->
        @can('execution.close')
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                <h2 class="text-base font-semibold text-slate-900 border-b border-slate-100 pb-3">Ejecutar Cierre Formal de Mes</h2>
                <p class="text-xs text-slate-500">Al cerrar un mes, el sistema computará el total devengado consolidado frente al planificado e impedirá procesar nuevos CURs para dicho período sin reapertura.</p>

                <form method="POST" action="{{ route('execution-closings.store') }}" onsubmit="return confirm('¿Está seguro de cerrar este mes de ejecución?');" class="grid gap-4 sm:grid-cols-4 items-end">
                    @csrf
                    <div>
                        <label for="close_year" class="block text-xs font-semibold uppercase text-slate-700">Año *</label>
                        <input type="number" name="year" id="close_year" value="{{ old('year', date('Y')) }}" required min="2020" max="2100" class="mt-1.5 w-full rounded-lg border border-slate-300 p-2.5 text-sm focus:border-institutional-600 focus:outline-none">
                    </div>

                    <div>
                        <label for="close_month" class="block text-xs font-semibold uppercase text-slate-700">Mes *</label>
                        <select name="month" id="close_month" required class="mt-1.5 w-full rounded-lg border border-slate-300 p-2.5 text-sm focus:border-institutional-600 focus:outline-none">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ old('month', date('n')) == $m ? 'selected' : '' }}>
                                    Mes {{ $m }} - {{ ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'][$m - 1] }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label for="close_notes" class="block text-xs font-semibold uppercase text-slate-700">Observaciones</label>
                        <input type="text" name="notes" id="close_notes" value="{{ old('notes') }}" placeholder="Cierre ordinario de mes..." class="mt-1.5 w-full rounded-lg border border-slate-300 p-2.5 text-sm focus:border-institutional-600 focus:outline-none">
                    </div>

                    <div>
                        <button type="submit" class="w-full rounded-lg bg-institutional-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-institutional-800 focus:outline-none focus:ring-2 focus:ring-institutional-600">
                            Cerrar Período Mensual
                        </button>
                    </div>
                </form>
            </div>
        @endcan

        <!-- Historial de Cierres -->
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-6">
                <h2 class="text-base font-semibold text-slate-900">Historial de Cierres Mensuales</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase text-slate-500">
                        <tr>
                            <th class="px-6 py-3.5">Período (Año - Mes)</th>
                            <th class="px-6 py-3.5 text-right">Total Planificado ($)</th>
                            <th class="px-6 py-3.5 text-right">Total Devengado ($)</th>
                            <th class="px-6 py-3.5 text-right">% Cumplimiento</th>
                            <th class="px-6 py-3.5">Estado</th>
                            <th class="px-6 py-3.5">Cerrado Por / Fecha</th>
                            <th class="px-6 py-3.5">Notas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($closings as $cl)
                            @php
                                $percent = $cl->total_planned > 0 ? ($cl->total_accrued / $cl->total_planned) * 100 : 0;
                            @endphp
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-6 py-4 font-bold text-slate-900">
                                    {{ $cl->year }} - Mes {{ str_pad((string) $cl->month, 2, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="px-6 py-4 text-right font-mono font-semibold text-slate-700">
                                    ${{ number_format($cl->total_planned, 2) }}
                                </td>
                                <td class="px-6 py-4 text-right font-mono font-bold text-emerald-700">
                                    ${{ number_format($cl->total_accrued, 2) }}
                                </td>
                                <td class="px-6 py-4 text-right font-mono text-xs font-semibold {{ $percent >= 80 ? 'text-emerald-700' : 'text-amber-600' }}">
                                    {{ number_format($percent, 1) }}%
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-800">
                                        {{ $cl->status->value }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-600">
                                    {{ $cl->user?->name ?? 'Usuario' }}<br>
                                    <span class="text-[10px] text-slate-400">{{ $cl->closed_at?->format('d/m/Y H:i') }}</span>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500">
                                    {{ $cl->notes ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-sm text-slate-500">
                                    No hay cierres mensuales registrados aún.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($closings->hasPages())
                <div class="border-t border-slate-200 bg-slate-50 px-6 py-3">
                    {{ $closings->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
