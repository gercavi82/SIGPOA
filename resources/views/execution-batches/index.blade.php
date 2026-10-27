<x-layouts.app title="Lotes de Importación eSIGEF">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Lotes de Importación eSIGEF</h1>
                <p class="text-sm text-slate-500">Carga de archivos CSV de CURs detallados, validación y conciliación de inconsistencias.</p>
            </div>
            @can('execution.import')
                <a href="{{ route('execution-batches.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-institutional-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-institutional-800 focus:outline-none focus:ring-2 focus:ring-institutional-600">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                    </svg>
                    Importar Archivo CSV eSIGEF
                </a>
            @endcan
        </div>

        @if(session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase text-slate-500">
                        <tr>
                            <th class="px-6 py-3.5">Código Lote</th>
                            <th class="px-6 py-3.5">Archivo / Hash SHA-256</th>
                            <th class="px-6 py-3.5">Año / Mes</th>
                            <th class="px-6 py-3.5 text-center">Filas (Total / Válidas / Inconsistentes)</th>
                            <th class="px-6 py-3.5">Estado</th>
                            <th class="px-6 py-3.5">Subido Por</th>
                            <th class="px-6 py-3.5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($batches as $b)
                            @php
                                $badgeStyle = match($b->status->value) {
                                    'PROCESADO' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    'VALIDADO' => 'bg-sky-100 text-sky-800 border-sky-200',
                                    'PARCIAL' => 'bg-amber-100 text-amber-800 border-amber-200',
                                    'FALLIDO' => 'bg-rose-100 text-rose-800 border-rose-200',
                                    default => 'bg-slate-100 text-slate-800 border-slate-200',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-6 py-4 font-mono font-bold text-slate-900">
                                    <a href="{{ route('execution-batches.show', $b) }}" class="text-institutional-700 hover:underline">
                                        {{ $b->batch_code }}
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-medium text-slate-800 text-xs block">{{ $b->original_filename }}</span>
                                    <span class="font-mono text-[10px] text-slate-400 block">{{ Str::limit($b->file_hash, 16) }}...</span>
                                </td>
                                <td class="px-6 py-4 text-xs font-semibold text-slate-700">
                                    {{ $b->year }} @if($b->month) · Mes {{ $b->month }} @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="inline-flex items-center gap-1.5 text-xs font-mono">
                                        <span class="font-bold text-slate-900">{{ $b->total_rows }}</span> /
                                        <span class="text-emerald-700 font-bold">{{ $b->valid_rows }}</span> /
                                        <span class="text-amber-700 font-bold">{{ $b->unmatched_rows }}</span> /
                                        <span class="text-rose-700 font-bold">{{ $b->invalid_rows }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400">Total / Vál / Inc / Inv</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $badgeStyle }}">
                                        {{ $b->status->value }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-600">
                                    {{ $b->importer?->name ?? 'Usuario' }}<br>
                                    <span class="text-[10px] text-slate-400">{{ $b->created_at?->format('d/m/Y H:i') }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('execution-batches.show', $b) }}" class="text-xs font-semibold text-institutional-700 hover:text-institutional-900">
                                        Inspeccionar →
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-sm text-slate-500">
                                    No se han importado lotes de ejecución eSIGEF aún.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($batches->hasPages())
                <div class="border-t border-slate-200 bg-slate-50 px-6 py-3">
                    {{ $batches->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
