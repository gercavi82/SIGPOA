<x-layouts.app title="Centro de Reportes y BI">
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Centro de Reportes Institucionales y BI</h1>
            <p class="text-sm text-slate-500">Generación y exportación de reportes ejecutivos, operativos y analíticos del ciclo POA.</p>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-2">
            <!-- Reporte 1: POA por Dirección -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm flex flex-col justify-between space-y-4">
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        <div class="grid size-10 place-items-center rounded-lg bg-institutional-50 text-institutional-700">
                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                            </svg>
                        </div>
                        <h2 class="text-base font-bold text-slate-900">1. POA por Dirección / Unidad</h2>
                    </div>
                    <p class="text-xs text-slate-600">Matriz detallada de actividades, objetivos, metas y montos anuales por unidad organizacional responsable.</p>
                </div>
                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <a href="{{ route('reports.poa-by-unit') }}" class="text-xs font-semibold text-institutional-700 hover:text-institutional-900">
                        Generar Reporte →
                    </a>
                    <a href="{{ route('reports.poa-by-unit', ['export' => 'csv']) }}" class="inline-flex items-center gap-1 text-xs font-bold text-slate-600 hover:text-slate-900">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                        </svg>
                        CSV
                    </a>
                </div>
            </div>

            <!-- Reporte 2: Ejecución Comparada -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm flex flex-col justify-between space-y-4">
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        <div class="grid size-10 place-items-center rounded-lg bg-emerald-50 text-emerald-700">
                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                            </svg>
                        </div>
                        <h2 class="text-base font-bold text-slate-900">2. Ejecución vs Planificado vs Saldo</h2>
                    </div>
                    <p class="text-xs text-slate-600">Balance comparativo entre el presupuesto programado, los montos devengados por CUR y el saldo disponible.</p>
                </div>
                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <a href="{{ route('reports.execution-comparison') }}" class="text-xs font-semibold text-institutional-700 hover:text-institutional-900">
                        Generar Reporte →
                    </a>
                    <a href="{{ route('reports.execution-comparison', ['export' => 'csv']) }}" class="inline-flex items-center gap-1 text-xs font-bold text-slate-600 hover:text-slate-900">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                        </svg>
                        CSV
                    </a>
                </div>
            </div>

            <!-- Reporte 3: Trazabilidad de Certificaciones -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm flex flex-col justify-between space-y-4">
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        <div class="grid size-10 place-items-center rounded-lg bg-sky-50 text-sky-700">
                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                            </svg>
                        </div>
                        <h2 class="text-base font-bold text-slate-900">3. Certificaciones POA y Presupuestarias</h2>
                    </div>
                    <p class="text-xs text-slate-600">Cruce integral entre las certificaciones POA (CERAR2) y las emitidas por eSIGEF / Financiero (CERA01).</p>
                </div>
                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <a href="{{ route('reports.certifications') }}" class="text-xs font-semibold text-institutional-700 hover:text-institutional-900">
                        Generar Reporte →
                    </a>
                    <a href="{{ route('reports.certifications', ['export' => 'csv']) }}" class="inline-flex items-center gap-1 text-xs font-bold text-slate-600 hover:text-slate-900">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                        </svg>
                        CSV
                    </a>
                </div>
            </div>

            <!-- Reporte 4: Modificaciones Presupuestarias -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm flex flex-col justify-between space-y-4">
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        <div class="grid size-10 place-items-center rounded-lg bg-purple-50 text-purple-700">
                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                            </svg>
                        </div>
                        <h2 class="text-base font-bold text-slate-900">4. Modificaciones y Reformas eSIGEF</h2>
                    </div>
                    <p class="text-xs text-slate-600">Historial de solicitudes de modificación (sin/con afectación, asignación de recursos) y CURs de reforma.</p>
                </div>
                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <a href="{{ route('reports.modifications') }}" class="text-xs font-semibold text-institutional-700 hover:text-institutional-900">
                        Generar Reporte →
                    </a>
                    <a href="{{ route('reports.modifications', ['export' => 'csv']) }}" class="inline-flex items-center gap-1 text-xs font-bold text-slate-600 hover:text-slate-900">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                        </svg>
                        CSV
                    </a>
                </div>
            </div>
        </div>

        <!-- Panel de Integración Analítica BI (Superset / Airflow) -->
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-3">
            <h2 class="text-base font-semibold text-slate-900">Integración con Plataformas de Inteligencia de Negocios (BI)</h2>
            <p class="text-xs text-slate-600 leading-relaxed">
                El sistema proporciona una proyección analítica desacoplada equivalente a la vista legacy <code>V_PRO_SIGPOA_EJE</code>. Esta capa permite que las herramientas como Apache Superset o Airflow consuman los datos consolidados sin comprometer las transacciones del núcleo OLTP.
            </p>
            <div class="pt-2">
                <a href="{{ route('analytics.dataset') }}" target="_blank" class="inline-flex items-center gap-2 rounded-lg bg-slate-800 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-900">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" />
                    </svg>
                    Consultar Endpoint Analítico JSON (V_PRO_SIGPOA_EJE)
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
