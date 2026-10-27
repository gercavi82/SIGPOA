<x-layouts.app title="Lote eSIGEF: {{ $batch->batch_code }}">
    <div class="max-w-6xl space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="{{ route('execution-batches.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-institutional-700 hover:text-institutional-800">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Volver a Lotes de Ejecución
                </a>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ $batch->batch_code }}</h1>
                    @php
                        $badgeClass = match($batch->status->value) {
                            'PROCESADO' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'VALIDADO' => 'bg-sky-100 text-sky-800 border-sky-200',
                            'PARCIAL' => 'bg-amber-100 text-amber-800 border-amber-200',
                            'FALLIDO' => 'bg-rose-100 text-rose-800 border-rose-200',
                            default => 'bg-slate-100 text-slate-800 border-slate-200',
                        };
                    @endphp
                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-bold {{ $badgeClass }}">
                        {{ $batch->status->value }}
                    </span>
                </div>
            </div>

            @can('execution.process')
                @if(in_array($batch->status->value, ['VALIDADO', 'PARCIAL', 'PENDIENTE']) && ($batch->valid_rows > 0 || $batch->rows->where('status', 'CONCILIADO')->count() > 0))
                    <form method="POST" action="{{ route('execution-batches.process', $batch) }}" onsubmit="return confirm('¿Confirmar procesamiento e impacto de devengados en el POA?');">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-emerald-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            Procesar e Impactar Devengados
                        </button>
                    </form>
                @endif
            @endcan
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

        <!-- Tarjetas de Resumen -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <span class="text-xs font-semibold uppercase text-slate-500">Total Filas</span>
                <p class="mt-1 text-2xl font-bold font-mono text-slate-900">{{ $batch->total_rows }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <span class="text-xs font-semibold uppercase text-slate-500">Filas Válidas</span>
                <p class="mt-1 text-2xl font-bold font-mono text-emerald-700">{{ $batch->valid_rows }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <span class="text-xs font-semibold uppercase text-slate-500">Por Conciliar (Inconsistentes)</span>
                <p class="mt-1 text-2xl font-bold font-mono text-amber-600">{{ $batch->unmatched_rows }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <span class="text-xs font-semibold uppercase text-slate-500">Filas Inválidas</span>
                <p class="mt-1 text-2xl font-bold font-mono text-rose-600">{{ $batch->invalid_rows }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <span class="text-xs font-semibold uppercase text-slate-500">Devengados Procesados</span>
                <p class="mt-1 text-2xl font-bold font-mono text-sky-700">{{ $batch->processed_rows }}</p>
            </div>
        </div>

        <!-- Ficha de Metadatos del Archivo -->
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm text-xs text-slate-600 space-y-1.5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <span><strong>Archivo Origen:</strong> {{ $batch->original_filename }}</span>
                <span><strong>Importado por:</strong> {{ $batch->importer?->name }} el {{ $batch->created_at?->format('d/m/Y H:i') }}</span>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-2">
                <span class="font-mono text-slate-400"><strong>SHA-256 Checksum:</strong> {{ $batch->file_hash }}</span>
                @if($batch->processed_at)
                    <span class="text-emerald-700 font-semibold">Procesado por {{ $batch->processor?->name }} el {{ $batch->processed_at?->format('d/m/Y H:i') }}</span>
                @endif
            </div>
        </div>

        <!-- Filtro por Estado de Fila -->
        <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-3 text-xs font-medium">
            <a href="{{ route('execution-batches.show', $batch) }}" class="rounded-lg px-3 py-1.5 {{ empty($currentFilter) ? 'bg-institutional-700 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Todas las Filas ({{ $batch->total_rows }})
            </a>
            <a href="{{ route('execution-batches.show', [$batch, 'status' => 'NO_CONCILIADO']) }}" class="rounded-lg px-3 py-1.5 {{ $currentFilter === 'NO_CONCILIADO' ? 'bg-amber-700 text-white font-bold' : 'bg-amber-50 text-amber-800 hover:bg-amber-100' }}">
                Inconsistentes / Por Conciliar ({{ $batch->unmatched_rows }})
            </a>
            <a href="{{ route('execution-batches.show', [$batch, 'status' => 'VALIDO']) }}" class="rounded-lg px-3 py-1.5 {{ $currentFilter === 'VALIDO' ? 'bg-emerald-700 text-white font-bold' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                Válidas ({{ $batch->valid_rows }})
            </a>
            <a href="{{ route('execution-batches.show', [$batch, 'status' => 'PROCESADO']) }}" class="rounded-lg px-3 py-1.5 {{ $currentFilter === 'PROCESADO' ? 'bg-sky-700 text-white font-bold' : 'bg-sky-50 text-sky-800 hover:bg-sky-100' }}">
                Procesadas ({{ $batch->processed_rows }})
            </a>
            <a href="{{ route('execution-batches.show', [$batch, 'status' => 'INVALIDO']) }}" class="rounded-lg px-3 py-1.5 {{ $currentFilter === 'INVALIDO' ? 'bg-rose-700 text-white font-bold' : 'bg-rose-50 text-rose-800 hover:bg-rose-100' }}">
                Inválidas ({{ $batch->invalid_rows }})
            </a>
        </div>

        <!-- Tabla de Filas de Lote -->
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase text-slate-500">
                        <tr>
                            <th class="px-5 py-3.5">#</th>
                            <th class="px-5 py-3.5">CUR / Línea</th>
                            <th class="px-5 py-3.5">Fecha</th>
                            <th class="px-5 py-3.5">Actividad POA</th>
                            <th class="px-5 py-3.5">Certif. Presupuestaria</th>
                            <th class="px-5 py-3.5 text-right">Monto ($)</th>
                            <th class="px-5 py-3.5">Estado</th>
                            <th class="px-5 py-3.5">Acción / Conciliación</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($rows as $row)
                            @php
                                $rowBadge = match($row->status->value) {
                                    'VALIDO' => 'bg-emerald-100 text-emerald-800',
                                    'CONCILIADO' => 'bg-sky-100 text-sky-800',
                                    'PROCESADO' => 'bg-slate-100 text-slate-800',
                                    'NO_CONCILIADO' => 'bg-amber-100 text-amber-800',
                                    'INVALIDO' => 'bg-rose-100 text-rose-800',
                                    default => 'bg-slate-100 text-slate-700',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-5 py-3.5 text-xs font-mono text-slate-400 font-bold">{{ $row->row_number }}</td>
                                <td class="px-5 py-3.5 font-mono text-xs font-bold text-slate-900">
                                    {{ $row->cur_number }} <span class="text-slate-400">#{{ $row->cur_line }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-xs text-slate-600">
                                    {{ $row->accrual_date?->format('d/m/Y') ?? 'N/A' }}
                                </td>
                                <td class="px-5 py-3.5 text-xs">
                                    @if($row->poaActivity)
                                        <span class="font-semibold text-slate-900">{{ $row->poaActivity->code ?? $row->poaActivity->number }}</span>
                                        <span class="block text-[11px] text-slate-500">{{ Str::limit($row->poaActivity->name ?? $row->poaActivity->description, 35) }}</span>
                                    @else
                                        <span class="text-rose-600 font-semibold italic">{{ $row->activity_code ? "Cód: {$row->activity_code} (No hallado)" : 'Sin código' }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-xs font-mono">
                                    @if($row->budgetCertification)
                                        <span class="font-semibold text-slate-800">{{ $row->budgetCertification->certification_number }}</span>
                                    @elseif($row->certification_number)
                                        <span class="text-slate-500">{{ $row->certification_number }}</span>
                                    @else
                                        <span class="text-slate-400 italic">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono font-bold text-slate-900">
                                    ${{ number_format($row->amount, 2) }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold {{ $rowBadge }}">
                                        {{ $row->status->value }}
                                    </span>
                                    @if($row->error_message)
                                        <span class="block text-[10px] text-rose-600 mt-0.5">{{ $row->error_message }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-xs">
                                    @if(in_array($row->status->value, ['NO_CONCILIADO', 'INVALIDO']) && $batch->status->value !== 'PROCESADO')
                                        <form method="POST" action="{{ route('execution-reconciliation.update', $row) }}" class="space-y-1.5">
                                            @csrf
                                            @method('PUT')
                                            <select name="poa_activity_id" required class="w-full rounded border border-slate-300 p-1 text-[11px]">
                                                <option value="">-- Asignar Actividad POA --</option>
                                                @foreach($activities as $act)
                                                    <option value="{{ $act->id }}">{{ $act->code ?? $act->number }} - {{ Str::limit($act->name ?? $act->description, 25) }}</option>
                                                @endforeach
                                            </select>
                                            <div class="flex gap-1">
                                                <select name="budget_certification_id" class="w-full rounded border border-slate-300 p-1 text-[11px]">
                                                    <option value="">-- CP (Opcional) --</option>
                                                    @foreach($certifications as $cert)
                                                        <option value="{{ $cert->id }}">{{ $cert->certification_number }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="rounded bg-institutional-700 px-2 py-1 text-[11px] font-bold text-white hover:bg-institutional-800">
                                                    Conciliar
                                                </button>
                                            </div>
                                        </form>
                                    @elseif($row->status->value === 'CONCILIADO')
                                        <span class="text-sky-700 font-semibold text-[11px]">✓ Conciliado manualmente</span>
                                    @elseif($row->status->value === 'PROCESADO')
                                        <span class="text-slate-400 text-[11px]">Devengado generado</span>
                                    @else
                                        <span class="text-emerald-700 font-semibold text-[11px]">Listo para procesar</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-10 text-center text-xs text-slate-500">
                                    No hay filas en este lote bajo el filtro seleccionado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($rows->hasPages())
                <div class="border-t border-slate-200 bg-slate-50 px-6 py-3">
                    {{ $rows->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
