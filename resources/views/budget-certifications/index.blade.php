<x-layouts.app title="Certificaciones Presupuestarias">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Certificaciones Presupuestarias (CERA01)</h1>
                <p class="text-sm text-slate-500">Registro y trazabilidad de certificaciones emitidas por la Dirección Financiera / eSIGEF.</p>
            </div>
            @canany(['certification.create', 'execution.import', 'modification.verify_budget'])
                <a href="{{ route('budget-certifications.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-institutional-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-institutional-800 focus:outline-none focus:ring-2 focus:ring-institutional-600">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Nueva Certificación Presupuestaria
                </a>
            @endcanany
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
                            <th class="px-6 py-3.5">Nro. Certificación</th>
                            <th class="px-6 py-3.5">Año / Fecha</th>
                            <th class="px-6 py-3.5">Concepto / Objeto</th>
                            <th class="px-6 py-3.5">Certificación POA</th>
                            <th class="px-6 py-3.5 text-right">Monto Total ($)</th>
                            <th class="px-6 py-3.5">Estado</th>
                            <th class="px-6 py-3.5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($certifications as $c)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-6 py-4 font-mono font-bold text-slate-900">
                                    <a href="{{ route('budget-certifications.show', $c) }}" class="text-institutional-700 hover:underline">
                                        {{ $c->certification_number }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-600">
                                    <span class="font-bold text-slate-800">{{ $c->year }}</span><br>
                                    {{ $c->certification_date?->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-700">
                                    <span class="line-clamp-2">{{ $c->concept ?? 'Sin concepto registrado' }}</span>
                                    <span class="text-[11px] text-slate-400">Líneas: {{ $c->lines->count() }}</span>
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    @if($c->poaCertification)
                                        <span class="font-semibold text-slate-800">{{ $c->poaCertification->number }}</span>
                                    @else
                                        <span class="italic text-slate-400">Sin vincular</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-mono font-semibold text-slate-900">
                                    ${{ number_format($c->total_amount, 2) }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">
                                        {{ $c->status->value }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('budget-certifications.show', $c) }}" class="text-xs font-semibold text-institutional-700 hover:text-institutional-900">
                                        Ver Detalle →
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-sm text-slate-500">
                                    No se encontraron certificaciones presupuestarias registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($certifications->hasPages())
                <div class="border-t border-slate-200 bg-slate-50 px-6 py-3">
                    {{ $certifications->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
