<x-layouts.app title="Tablero de Control - SIGPOA">
    <div class="space-y-8">
        <!-- Encabezado y Filtro -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-institutional-600">Dirección General de Aviación Civil</p>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Tablero de Control y Ejecución POA</h1>
                <p class="text-xs text-slate-500">Métricas consolidadas, ejecución presupuestaria y estado operativo del ciclo anual.</p>
            </div>

            <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-2">
                <select name="year" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm focus:border-institutional-600 focus:outline-none">
                    @for($y = (int) date('Y') + 1; $y >= (int) date('Y') - 2; $y--)
                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>Año {{ $y }}</option>
                    @endfor
                </select>

                <select name="unit_id" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm focus:border-institutional-600 focus:outline-none">
                    <option value="">-- Toda la Institución --</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" {{ $selectedUnitId == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>

                <button type="submit" class="rounded-lg bg-institutional-700 px-4 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-institutional-800 focus:outline-none focus:ring-2 focus:ring-institutional-600">
                    Filtrar
                </button>
            </form>
        </div>

        <!-- 5 Tarjetas Principales de KPI -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <span class="text-xs font-semibold uppercase text-slate-500">Total POA Vigente</span>
                <p class="mt-2 text-2xl font-bold font-mono text-slate-900">${{ number_format($kpis['total_poa'], 2) }}</p>
                <span class="text-[11px] text-slate-400 mt-1 block">{{ $kpis['activities_count'] }} Actividades registradas</span>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <span class="text-xs font-semibold uppercase text-slate-500">Total Planificado</span>
                <p class="mt-2 text-2xl font-bold font-mono text-sky-700">${{ number_format($kpis['total_planned'], 2) }}</p>
                <span class="text-[11px] text-slate-400 mt-1 block">Programación mensual acumulada</span>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <span class="text-xs font-semibold uppercase text-slate-500">Total Devengado (CURs)</span>
                <p class="mt-2 text-2xl font-bold font-mono text-emerald-700">${{ number_format($kpis['total_accrued'], 2) }}</p>
                <span class="text-[11px] text-slate-400 mt-1 block">Ejecución efectiva eSIGEF</span>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <span class="text-xs font-semibold uppercase text-slate-500">Saldo Disponible</span>
                <p class="mt-2 text-2xl font-bold font-mono text-slate-700">${{ number_format($kpis['balance'], 2) }}</p>
                <span class="text-[11px] text-slate-400 mt-1 block">Por comprometer / devengar</span>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <span class="text-xs font-semibold uppercase text-slate-500">% Cumplimiento Global</span>
                <div class="mt-2 flex items-baseline justify-between">
                    <p class="text-2xl font-bold font-mono {{ $kpis['execution_rate'] >= 75 ? 'text-emerald-700' : ($kpis['execution_rate'] >= 50 ? 'text-sky-700' : 'text-amber-600') }}">
                        {{ number_format($kpis['execution_rate'], 1) }}%
                    </p>
                </div>
                <!-- Barra de Progreso -->
                <div class="mt-2 h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full bg-institutional-600 rounded-full" style="width: {{ min(100, $kpis['execution_rate']) }}%"></div>
                </div>
            </div>
        </div>

        <!-- Alertas Operativas y Trámites Pendientes -->
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="flex items-center justify-between rounded-xl border border-amber-200 bg-amber-50/50 p-4">
                <div class="flex items-center gap-3">
                    <div class="grid size-10 place-items-center rounded-lg bg-amber-100 text-amber-800">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase text-amber-900">Certificaciones en Trámite</span>
                        <p class="text-xs text-amber-700">Pendientes de revisión / firma</p>
                    </div>
                </div>
                <span class="text-xl font-bold font-mono text-amber-900">{{ $kpis['pending_certifications_count'] }}</span>
            </div>

            <div class="flex items-center justify-between rounded-xl border border-sky-200 bg-sky-50/50 p-4">
                <div class="flex items-center gap-3">
                    <div class="grid size-10 place-items-center rounded-lg bg-sky-100 text-sky-800">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase text-sky-900">Modificaciones en Revisión</span>
                        <p class="text-xs text-sky-700">En análisis técnico o reforma</p>
                    </div>
                </div>
                <span class="text-xl font-bold font-mono text-sky-900">{{ $kpis['pending_modifications_count'] }}</span>
            </div>

            <div class="flex items-center justify-between rounded-xl border border-rose-200 bg-rose-50/50 p-4">
                <div class="flex items-center gap-3">
                    <div class="grid size-10 place-items-center rounded-lg bg-rose-100 text-rose-800">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.875A9.375 9.375 0 0 0 2.625 12c0 5.178 4.197 9.375 9.375 9.375s9.375-4.197 9.375-9.375A9.375 9.375 0 0 0 12 1.125ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase text-rose-900">Solicitudes Observadas</span>
                        <p class="text-xs text-rose-700">Devueltas para corrección</p>
                    </div>
                </div>
                <span class="text-xl font-bold font-mono text-rose-900">{{ $kpis['observed_requests_count'] }}</span>
            </div>
        </div>

        <!-- Ejecución Presupuestaria por Dirección de Área -->
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-6 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Ejecución Presupuestaria por Dirección de Área</h2>
                    <p class="text-xs text-slate-500">Distribución de recursos asignados vs devengados por unidad ejecutora.</p>
                </div>
                @can('reports.view')
                    <a href="{{ route('reports.poa-by-unit') }}" class="text-xs font-semibold text-institutional-700 hover:text-institutional-900">
                        Ver Reporte Detallado →
                    </a>
                @endcan
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase text-slate-500">
                        <tr>
                            <th class="px-6 py-3.5">Dirección / Unidad</th>
                            <th class="px-6 py-3.5 text-center">Actividades</th>
                            <th class="px-6 py-3.5 text-right">Monto Asignado ($)</th>
                            <th class="px-6 py-3.5 text-right">Devengado ($)</th>
                            <th class="px-6 py-3.5 text-right">Saldo ($)</th>
                            <th class="px-6 py-3.5 text-center w-48">% Cumplimiento</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($unitExecution as $u)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-6 py-4">
                                    <span class="font-bold text-slate-900 text-xs">{{ $u['unit_name'] }}</span>
                                    <span class="block font-mono text-[10px] text-slate-400">Código: {{ $u['unit_code'] }}</span>
                                </td>
                                <td class="px-6 py-4 text-center font-mono text-xs text-slate-600">
                                    {{ $u['activities_count'] }}
                                </td>
                                <td class="px-6 py-4 text-right font-mono font-semibold text-slate-900">
                                    ${{ number_format($u['total_poa'], 2) }}
                                </td>
                                <td class="px-6 py-4 text-right font-mono font-semibold text-emerald-700">
                                    ${{ number_format($u['total_accrued'], 2) }}
                                </td>
                                <td class="px-6 py-4 text-right font-mono text-xs text-slate-600">
                                    ${{ number_format($u['balance'], 2) }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                                            <div class="h-full rounded-full {{ $u['execution_rate'] >= 75 ? 'bg-emerald-500' : ($u['execution_rate'] >= 50 ? 'bg-sky-500' : 'bg-amber-500') }}" style="width: {{ min(100, $u['execution_rate']) }}%"></div>
                                        </div>
                                        <span class="font-mono text-xs font-bold text-slate-700 w-12 text-right">{{ number_format($u['execution_rate'], 1) }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-xs text-slate-500">No hay datos de ejecución para mostrar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Desglose Mensual (12 Meses) -->
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-6">
                <h2 class="text-base font-semibold text-slate-900">Programación vs Ejecución Mensual (Ejercicio {{ $selectedYear }})</h2>
                <p class="text-xs text-slate-500">Comparación de la planificación mensual frente al devengado registrado por CURs.</p>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                    @foreach($monthlyComparison as $m)
                        <div class="rounded-lg border border-slate-200 bg-slate-50/50 p-3 space-y-1.5">
                            <span class="text-xs font-bold text-slate-800">{{ $m['month_name'] }}</span>
                            <div class="text-[11px]">
                                <span class="text-slate-500 block">Plan: <strong class="font-mono text-slate-800">${{ number_format($m['planned'], 0) }}</strong></span>
                                <span class="text-slate-500 block">Dev: <strong class="font-mono text-emerald-700">${{ number_format($m['accrued'], 0) }}</strong></span>
                            </div>
                            <div class="mt-1 pt-1 border-t border-slate-200 flex justify-between text-[10px] font-semibold">
                                <span class="text-slate-400">Avance:</span>
                                <span class="{{ $m['rate'] >= 75 ? 'text-emerald-700' : 'text-slate-700' }} font-mono">{{ number_format($m['rate'], 1) }}%</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
