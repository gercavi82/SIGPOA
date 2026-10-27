<x-layouts.app title="Certificación Presupuestaria: {{ $certification->certification_number }}">
    <div class="max-w-5xl space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="{{ route('budget-certifications.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-institutional-700 hover:text-institutional-800">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Volver a Certificaciones Presupuestarias
                </a>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ $certification->certification_number }}</h1>
                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800 border border-emerald-200">
                        {{ $certification->status->value }}
                    </span>
                </div>
            </div>
        </div>

        @if(session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <!-- Ficha de Información -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <span class="text-xs font-semibold uppercase text-slate-500">Monto Total Certificado</span>
                <p class="mt-1 font-bold text-slate-900 font-mono text-lg text-emerald-700">${{ number_format($certification->total_amount, 2) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <span class="text-xs font-semibold uppercase text-slate-500">Año / Fecha Emisión</span>
                <p class="mt-1 font-bold text-slate-900 text-sm">{{ $certification->year }} · {{ $certification->certification_date?->format('d/m/Y') }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <span class="text-xs font-semibold uppercase text-slate-500">Certificación POA</span>
                <p class="mt-1 font-bold text-slate-900 text-sm">{{ $certification->poaCertification?->number ?? 'No vinculada' }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <span class="text-xs font-semibold uppercase text-slate-500">Registrado Por</span>
                <p class="mt-1 font-bold text-slate-900 text-sm">{{ $certification->creator?->name ?? 'Sistema' }}</p>
            </div>
        </div>

        <!-- Concepto -->
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Concepto / Justificación</h2>
            <p class="mt-2 text-sm text-slate-800 leading-relaxed">{{ $certification->concept ?? 'Sin concepto registrado.' }}</p>
        </div>

        <!-- Tabla de Líneas de Detalle (LINAR6) -->
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-6">
                <h2 class="text-base font-semibold text-slate-900">Líneas de Certificación Presupuestaria (LINAR6)</h2>
                <p class="text-xs text-slate-500">Detalle de ítems y actividades POA vinculadas a esta certificación.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase text-slate-500">
                        <tr>
                            <th class="px-6 py-3.5">Línea</th>
                            <th class="px-6 py-3.5">Actividad POA</th>
                            <th class="px-6 py-3.5">Fuente</th>
                            <th class="px-6 py-3.5">Concepto</th>
                            <th class="px-6 py-3.5 text-right">Monto ($)</th>
                            <th class="px-6 py-3.5">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($certification->lines as $line)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-6 py-4 font-mono font-bold text-slate-500">{{ $line->line_number }}</td>
                                <td class="px-6 py-4">
                                    @if($line->poaActivity)
                                        <span class="font-semibold text-slate-900">{{ $line->poaActivity->code ?? $line->poaActivity->number }}</span>
                                        <span class="block text-xs text-slate-500">{{ Str::limit($line->poaActivity->name ?? $line->poaActivity->description, 45) }}</span>
                                    @else
                                        <span class="italic text-xs text-slate-400">Sin actividad vinculada</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs font-mono text-slate-600">{{ $line->funding_source ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-xs text-slate-700">{{ $line->concept ?? '-' }}</td>
                                <td class="px-6 py-4 text-right font-mono font-semibold text-slate-900">${{ number_format($line->amount, 2) }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-700">
                                        {{ $line->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-xs text-slate-500">Sin líneas registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Devengados / CURs vinculados -->
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-6">
                <h2 class="text-base font-semibold text-slate-900">Devengados y CURs Asociados (LINAR1)</h2>
                <p class="text-xs text-slate-500">Pagos e importaciones procesadas contra esta certificación.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase text-slate-500">
                        <tr>
                            <th class="px-6 py-3.5">Nro. CUR</th>
                            <th class="px-6 py-3.5">Fecha</th>
                            <th class="px-6 py-3.5">Mes / Año</th>
                            <th class="px-6 py-3.5 text-right">Monto Devengado ($)</th>
                            <th class="px-6 py-3.5">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($certification->accruals as $acc)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-6 py-4 font-mono font-bold text-slate-900">{{ $acc->cur_number }} #{{ $acc->cur_line }}</td>
                                <td class="px-6 py-4 text-xs text-slate-600">{{ $acc->accrual_date?->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-xs font-semibold text-slate-700">{{ $acc->month }}/{{ $acc->year }}</td>
                                <td class="px-6 py-4 text-right font-mono font-semibold text-slate-900">${{ number_format($acc->accrued_amount, 2) }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-800">
                                        {{ $acc->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-xs text-slate-400 italic">No se han registrado devengados para esta certificación.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
