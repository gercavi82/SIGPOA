<x-layouts.app title="Modificaciones POA">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Modificaciones POA</h1>
                <p class="mt-1 text-sm text-slate-500">Gestión y seguimiento de solicitudes de modificación presupuestaria y técnica.</p>
            </div>
            @can('modification.create')
                <a href="{{ route('modifications.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-institutional-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-institutional-800 focus:outline-none focus:ring-2 focus:ring-institutional-600">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Nueva Solicitud
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
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th scope="col" class="px-6 py-4">Número / Tipo</th>
                            <th scope="col" class="px-6 py-4">Unidad / Proceso</th>
                            <th scope="col" class="px-6 py-4">Líneas</th>
                            <th scope="col" class="px-6 py-4">Estado</th>
                            <th scope="col" class="px-6 py-4">eSIGEF</th>
                            <th scope="col" class="px-6 py-4">Fecha</th>
                            <th scope="col" class="px-6 py-4 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($requests as $r)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="font-bold text-slate-900">{{ $r->number }}</span>
                                    <span class="block text-xs text-slate-500 mt-0.5">{{ $r->type->value }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-medium text-slate-900">{{ $r->organizationalUnit?->name ?? 'N/A' }}</span>
                                    <span class="block text-xs text-slate-500">{{ $r->process?->phase?->value ?? 'Fase' }} ({{ $r->process?->planningPeriod?->year ?? '' }})</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-md bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700">
                                        {{ $r->lines_count }} {{ $r->lines_count === 1 ? 'línea' : 'líneas' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $badgeClasses = match($r->status->value) {
                                            'APROBADA' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                            'BORRADOR' => 'bg-slate-100 text-slate-800 border-slate-200',
                                            'DEVUELTA' => 'bg-amber-100 text-amber-800 border-amber-200',
                                            'NEGADA' => 'bg-rose-100 text-rose-800 border-rose-200',
                                            'VIABLE', 'AUTORIZADA' => 'bg-sky-100 text-sky-800 border-sky-200',
                                            'REFORMA_PENDIENTE' => 'bg-purple-100 text-purple-800 border-purple-200',
                                            default => 'bg-blue-100 text-blue-800 border-blue-200',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $badgeClasses }}">
                                        {{ $r->status->value }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($r->requires_esigef)
                                        @if($r->esigef_reform_number && $r->reform_cur)
                                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700">
                                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                                </svg>
                                                Ref: {{ $r->esigef_reform_number }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700 border border-amber-200">
                                                Pendiente
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-xs text-slate-400">No aplica</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500">
                                    {{ $r->created_at?->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <a href="{{ route('modifications.show', $r) }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-institutional-700 focus:outline-none focus:ring-2 focus:ring-institutional-600">
                                        Ver detalle
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-slate-500">
                                    <svg width="40" height="40" aria-hidden="true" class="mx-auto h-10 w-10 shrink-0 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                    <p class="mt-2 text-sm font-medium">No se encontraron solicitudes de modificación.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($requests->hasPages())
                <div class="border-t border-slate-200 p-4">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
