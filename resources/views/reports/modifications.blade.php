<x-layouts.app title="Reporte de Modificaciones Presupuestarias">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-institutional-700 hover:text-institutional-800">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Volver al Centro de Reportes
                </a>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Reporte de Modificaciones Presupuestarias</h1>
            </div>

            <a href="{{ route('reports.modifications', ['year' => $selectedYear, 'unit_id' => $selectedUnitId, 'export' => 'csv']) }}" class="inline-flex items-center gap-2 rounded-lg bg-institutional-700 px-4 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-institutional-800 focus:outline-none focus:ring-2 focus:ring-institutional-600">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                </svg>
                Exportar CSV
            </a>
        </div>

        <!-- Filtros -->
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('reports.modifications') }}" class="flex flex-wrap items-end gap-4">
                <div>
                    <label for="filter_year" class="block text-xs font-semibold uppercase text-slate-600">Año Fiscal</label>
                    <select name="year" id="filter_year" class="mt-1 rounded-lg border border-slate-300 p-2 text-xs focus:border-institutional-600 focus:outline-none">
                        @for($y = (int) date('Y') + 1; $y >= (int) date('Y') - 2; $y--)
                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label for="filter_unit" class="block text-xs font-semibold uppercase text-slate-600">Dirección / Unidad</label>
                    <select name="unit_id" id="filter_unit" class="mt-1 rounded-lg border border-slate-300 p-2 text-xs focus:border-institutional-600 focus:outline-none">
                        <option value="">-- Toda la Institución --</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" {{ $selectedUnitId == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-900">
                    Aplicar Filtros
                </button>
            </form>
        </div>

        <!-- Tabla de Datos -->
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase text-slate-500">
                        <tr>
                            <th class="px-5 py-3.5">Nro. Solicitud</th>
                            <th class="px-5 py-3.5">Tipo</th>
                            <th class="px-5 py-3.5">Dirección</th>
                            <th class="px-5 py-3.5 text-right">Monto Débito ($)</th>
                            <th class="px-5 py-3.5 text-right">Monto Crédito ($)</th>
                            <th class="px-5 py-3.5">Reforma eSIGEF</th>
                            <th class="px-5 py-3.5">CUR Reforma</th>
                            <th class="px-5 py-3.5">Estado</th>
                            <th class="px-5 py-3.5">Solicitante</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($data as $row)
                            <tr class="hover:bg-slate-50/70 text-xs">
                                <td class="px-5 py-3.5 font-mono font-bold text-slate-900">{{ $row['numero'] }}</td>
                                <td class="px-5 py-3.5 font-semibold text-slate-800">{{ $row['tipo'] }}</td>
                                <td class="px-5 py-3.5 text-slate-700">{{ $row['direccion'] }}</td>
                                <td class="px-5 py-3.5 text-right font-mono font-bold text-slate-900">${{ number_format($row['monto_debito'], 2) }}</td>
                                <td class="px-5 py-3.5 text-right font-mono font-bold text-slate-900">${{ number_format($row['monto_credito'], 2) }}</td>
                                <td class="px-5 py-3.5 font-mono text-slate-800">{{ $row['reforma_esigef'] }}</td>
                                <td class="px-5 py-3.5 font-mono text-slate-800">{{ $row['cur_reforma'] }}</td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-700">
                                        {{ $row['estado'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-slate-600">{{ $row['solicitante'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-12 text-center text-xs text-slate-500">No se encontraron solicitudes de modificación registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
