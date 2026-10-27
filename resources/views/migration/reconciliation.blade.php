<x-layouts.app title="Convivencia y Reconciliación DB2">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-institutional-700 hover:text-institutional-800">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Volver al Tablero
                </a>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Convivencia y Reconciliación DB2 → PostgreSQL</h1>
                <p class="text-sm text-slate-500">Auditoría de integridad, verificación de conteos y sumatorias entre IBM i y el nuevo SIGPOA.</p>
            </div>

            <form method="GET" action="{{ route('migration.reconciliation') }}" class="flex items-center gap-2">
                <select name="year" onchange="this.form.submit()" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm focus:border-institutional-600 focus:outline-none">
                    @for($y = (int) date('Y') + 1; $y >= (int) date('Y') - 3; $y--)
                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>Ejercicio {{ $y }}</option>
                    @endfor
                </select>
            </form>
        </div>

        @if(session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <!-- Ficha de Estado de Reconciliación y Hash de Integridad -->
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <span class="text-xs font-semibold uppercase text-slate-500">Estado de Reconciliación</span>
                <div class="mt-2 flex items-center gap-2">
                    @if($report['overall_status'] === 'RECONCILIADO_OK')
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                            ✓ RECONCILIADO OK
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">
                            ⚠ DIFERENCIAS REGISTRADAS
                        </span>
                    @endif
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:col-span-2">
                <span class="text-xs font-semibold uppercase text-slate-500">Checksum de Integridad SHA-256</span>
                <p class="mt-2 font-mono text-xs font-bold text-slate-800 break-all">{{ $report['integrity_hash'] }}</p>
                <span class="text-[10px] text-slate-400 mt-1 block">Generado: {{ $report['generated_at'] }}</span>
            </div>
        </div>

        <!-- Matriz de Reconciliación por Tabla Legacy -->
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-6">
                <h2 class="text-base font-semibold text-slate-900">Matriz de Validación y Reconciliación de Datos (Ejercicio {{ $selectedYear }})</h2>
                <p class="text-xs text-slate-500">Comparación cuantitativa y financiera de tablas físicas DB2 frente a los modelos normalizados de PostgreSQL.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase text-slate-500">
                        <tr>
                            <th class="px-5 py-3.5">Tabla DB2 / Destino PG</th>
                            <th class="px-5 py-3.5">Clasificación de Relación</th>
                            <th class="px-5 py-3.5 text-center">Cant. DB2</th>
                            <th class="px-5 py-3.5 text-center">Cant. PG</th>
                            <th class="px-5 py-3.5 text-center">Dif. Cant.</th>
                            <th class="px-5 py-3.5 text-right">Monto DB2 ($)</th>
                            <th class="px-5 py-3.5 text-right">Monto PG ($)</th>
                            <th class="px-5 py-3.5 text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($report['tables'] as $t)
                            <tr class="hover:bg-slate-50/70 text-xs">
                                <td class="px-5 py-3.5">
                                    <span class="font-bold text-slate-900 block">{{ $t['legacy_table'] }}</span>
                                    <span class="font-mono text-[10px] text-slate-400">→ {{ $t['target_model'] }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-slate-600 font-medium">
                                    {{ $t['relationship_type'] }}
                                </td>
                                <td class="px-5 py-3.5 text-center font-mono font-bold text-slate-800">{{ $t['legacy_count'] }}</td>
                                <td class="px-5 py-3.5 text-center font-mono font-bold text-slate-800">{{ $t['pg_count'] }}</td>
                                <td class="px-5 py-3.5 text-center font-mono {{ $t['count_diff'] != 0 ? 'text-amber-600 font-bold' : 'text-slate-400' }}">
                                    {{ $t['count_diff'] }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono font-bold text-slate-900">${{ number_format($t['legacy_sum'], 2) }}</td>
                                <td class="px-5 py-3.5 text-right font-mono font-bold text-emerald-700">${{ number_format($t['pg_sum'], 2) }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    @if($t['status'] === 'CONCILIADO')
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">
                                            ✓ CONCILIADO
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800">
                                            ⚠ DIFERENCIA
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Leyenda de Clasificación de Relaciones (Sección 2 del Prompt Maestro) -->
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-3">
            <h2 class="text-base font-semibold text-slate-900">Principio Fundamental de Migración: Clasificación de Relaciones</h2>
            <p class="text-xs text-slate-600">
                Ninguna relación inferida (Tipo D) es convertida a clave foránea estricta sin previa validación funcional:
            </p>
            <div class="grid gap-3 sm:grid-cols-2 text-xs">
                <div class="rounded-lg bg-slate-50 p-3 border border-slate-200">
                    <span class="font-bold text-slate-900">A. CONFIRMADA FÍSICAMENTE</span>
                    <p class="text-slate-500 mt-1">Claves primarias y foráneas explícitas del modelo relacional.</p>
                </div>
                <div class="rounded-lg bg-slate-50 p-3 border border-slate-200">
                    <span class="font-bold text-slate-900">B. CONFIRMADA POR CLAVE / ACCESO LÓGICO</span>
                    <p class="text-slate-500 mt-1">Relaciones gobernadas por archivos lógicos DDS y claves compuestas (ej. PLAARC ↔ PLAAR1).</p>
                </div>
                <div class="rounded-lg bg-slate-50 p-3 border border-slate-200">
                    <span class="font-bold text-slate-900">C. CONFIRMADA FUNCIONALMENTE POR BPM</span>
                    <p class="text-slate-500 mt-1">Relaciones validadas por las reglas de los programas IBM i (ej. CERAR2 ↔ LINAR1).</p>
                </div>
                <div class="rounded-lg bg-slate-50 p-3 border border-slate-200">
                    <span class="font-bold text-slate-900">D. INFERIDA Y PENDIENTE DE VALIDACIÓN</span>
                    <p class="text-slate-500 mt-1">Relaciones estadísticas o no validadas; aisladas en el importador.</p>
                </div>
            </div>
        </div>

        <!-- Panel de Ejecución de Migración (Solo Admin) -->
        @can('admin.parameters')
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                <h2 class="text-base font-semibold text-slate-900 border-b border-slate-100 pb-3">Ejecutar Proceso de Migración Asistida</h2>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Este proceso extraerá los registros desde las tablas físicas DB2 de IBM i (vía adaptadores READ-ONLY), los mapeará a DTOs de dominio normalizados e insertará en PostgreSQL generando un nuevo reporte de reconciliación auditable.
                </p>

                <form method="POST" action="{{ route('migration.reconciliation.execute') }}" onsubmit="return confirm('¿Confirma la ejecución del proceso de migración para el ejercicio seleccionado?');" class="flex flex-wrap items-center gap-4">
                    @csrf
                    <input type="hidden" name="year" value="{{ $selectedYear }}">

                    <label class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                        <input type="checkbox" name="dry_run" value="1" class="rounded border-slate-300 text-institutional-600 focus:ring-institutional-500">
                        Modo Simulación / Dry-Run (Validar sin escribir en BD)
                    </label>

                    <button type="submit" class="rounded-lg bg-institutional-700 px-5 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-institutional-800 focus:outline-none focus:ring-2 focus:ring-institutional-600">
                        Iniciar Migración Ejercicio {{ $selectedYear }}
                    </button>
                </form>
            </div>
        @endcan
    </div>
</x-layouts.app>
