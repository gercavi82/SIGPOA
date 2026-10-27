<x-layouts.app title="Corregir Solicitud de Modificación">
    <div class="max-w-5xl space-y-6">
        <div>
            <a href="{{ route('modifications.show', $request) }}" class="inline-flex items-center gap-1 text-sm font-semibold text-institutional-700 hover:text-institutional-800">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Volver al detalle
            </a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Corregir Solicitud: {{ $request->number }}</h1>
            <p class="mt-1 text-sm text-slate-500">Actualice la justificación, líneas de modificación o mensualización. Al guardar se creará una nueva versión auditada.</p>
        </div>

        @if($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                <p class="font-semibold">Se encontraron errores en el formulario:</p>
                <ul class="mt-1.5 list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('modifications.update', $request) }}" id="modificationForm" class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="version" value="{{ $request->version }}">

            <!-- Datos Generales -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold text-slate-900 border-b border-slate-100 pb-3">1. Datos Generales</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="number" class="block text-xs font-semibold text-slate-700 uppercase">Número de Solicitud *</label>
                        <input type="text" name="number" id="number" value="{{ old('number', $request->number) }}" required class="mt-1.5 w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-institutional-600 focus:outline-none focus:ring-1 focus:ring-institutional-600">
                    </div>

                    <div>
                        <label for="type" class="block text-xs font-semibold text-slate-700 uppercase">Tipo de Modificación *</label>
                        <select name="type" id="type" required class="mt-1.5 w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-institutional-600 focus:outline-none focus:ring-1 focus:ring-institutional-600">
                            @foreach($types as $type)
                                <option value="{{ $type->value }}" @selected(old('type', $request->type->value) === $type->value)>
                                    {{ $type->value }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="poa_process_id" class="block text-xs font-semibold text-slate-700 uppercase">Proceso POA *</label>
                        <select name="poa_process_id" id="poa_process_id" required class="mt-1.5 w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-institutional-600 focus:outline-none focus:ring-1 focus:ring-institutional-600">
                            @foreach($processes as $p)
                                <option value="{{ $p->id }}" @selected(old('poa_process_id', $request->poa_process_id) == $p->id)>
                                    {{ $p->phase->value }} ({{ $p->planningPeriod?->year }}) - {{ $p->status->value }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="organizational_unit_id" class="block text-xs font-semibold text-slate-700 uppercase">Unidad Organizacional *</label>
                        <select name="organizational_unit_id" id="organizational_unit_id" required class="mt-1.5 w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-institutional-600 focus:outline-none focus:ring-1 focus:ring-institutional-600">
                            @foreach($units as $u)
                                <option value="{{ $u->id }}" @selected(old('organizational_unit_id', $request->organizational_unit_id) == $u->id)>
                                    {{ $u->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="justification" class="block text-xs font-semibold text-slate-700 uppercase">Justificación Técnica / Legal *</label>
                        <textarea name="justification" id="justification" rows="3" required class="mt-1.5 w-full rounded-lg border border-slate-300 p-3 text-sm focus:border-institutional-600 focus:outline-none focus:ring-1 focus:ring-institutional-600">{{ old('justification', $request->justification) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Líneas de Modificación y Mensualización -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">2. Líneas de Movimiento y Mensualización</h2>
                        <p class="text-xs text-slate-500">Defina las actividades origen (débito) y destino (crédito) con su correspondiente distribución mensual.</p>
                    </div>
                    <button type="button" id="btnAddLine" class="inline-flex items-center gap-1.5 rounded-lg border border-institutional-600 bg-white px-3 py-1.5 text-xs font-semibold text-institutional-700 hover:bg-institutional-50">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Agregar Línea
                    </button>
                </div>

                <div id="linesContainer" class="mt-4 space-y-4">
                    @php
                        $lines = old('lines', $request->lines->toArray());
                    @endphp

                    @foreach($lines as $lIdx => $line)
                        <div class="line-row rounded-xl border border-slate-200 bg-slate-50/50 p-4 space-y-4" data-index="{{ $lIdx }}">
                            <div class="flex items-center justify-between gap-2 border-b border-slate-200 pb-2">
                                <span class="text-xs font-bold text-institutional-700 uppercase tracking-wide">Línea #<span class="line-number">{{ $lIdx + 1 }}</span></span>
                                <button type="button" class="btn-remove-line text-xs font-semibold text-rose-600 hover:text-rose-800">
                                    Eliminar Línea
                                </button>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                <div>
                                    <label class="block text-xs font-medium text-slate-600">Actividad Origen (Débito)</label>
                                    <select name="lines[{{ $lIdx }}][source_activity_id]" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs focus:border-institutional-600 focus:outline-none">
                                        <option value="">-- Sin actividad origen --</option>
                                        @foreach($activities as $act)
                                            <option value="{{ $act->id }}" @selected(($line['source_activity_id'] ?? null) == $act->id)>
                                                {{ $act->number }} - {{ Str::limit($act->description, 40) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-slate-600">Actividad Destino (Crédito)</label>
                                    <select name="lines[{{ $lIdx }}][destination_activity_id]" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs focus:border-institutional-600 focus:outline-none">
                                        <option value="">-- Sin actividad destino --</option>
                                        @foreach($activities as $act)
                                            <option value="{{ $act->id }}" @selected(($line['destination_activity_id'] ?? null) == $act->id)>
                                                {{ $act->number }} - {{ Str::limit($act->description, 40) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-slate-600">Monto Débito ($) *</label>
                                    <input type="number" step="0.01" min="0" name="lines[{{ $lIdx }}][debit_amount]" value="{{ $line['debit_amount'] ?? 0 }}" required class="input-debit mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-800 focus:border-institutional-600 focus:outline-none">
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-slate-600">Monto Crédito ($) *</label>
                                    <input type="number" step="0.01" min="0" name="lines[{{ $lIdx }}][credit_amount]" value="{{ $line['credit_amount'] ?? 0 }}" required class="input-credit mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-800 focus:border-institutional-600 focus:outline-none">
                                </div>

                                <div class="sm:col-span-2 lg:col-span-4">
                                    <label class="block text-xs font-medium text-slate-600">Descripción / Concepto del Movimiento *</label>
                                    <input type="text" name="lines[{{ $lIdx }}][description]" value="{{ $line['description'] ?? '' }}" required class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs focus:border-institutional-600 focus:outline-none">
                                </div>
                            </div>

                            <!-- Mensualización Accordion -->
                            <div class="border-t border-slate-200 pt-3">
                                <button type="button" class="btn-toggle-months inline-flex items-center gap-1 text-xs font-semibold text-institutional-600 hover:text-institutional-800">
                                    <svg class="size-3.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                    <span>Configurar Mensualización (12 meses)</span>
                                </button>

                                <div class="months-grid mt-3 hidden rounded-lg border border-slate-200 bg-white p-3">
                                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 lg:grid-cols-6 text-xs">
                                        @php
                                            $monthNames = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                                            $monthsMap = collect($line['months'] ?? [])->keyBy('month');
                                        @endphp
                                        @foreach($monthNames as $mIdx => $mName)
                                            @php
                                                $monthNum = $mIdx + 1;
                                                $mRow = $monthsMap->get($monthNum);
                                            @endphp
                                            <div class="rounded border border-slate-100 bg-slate-50/70 p-2">
                                                <span class="font-bold text-slate-700">{{ $monthNum }}. {{ $mName }}</span>
                                                <input type="hidden" name="lines[{{ $lIdx }}][months][{{ $mIdx }}][month]" value="{{ $monthNum }}">
                                                <div class="mt-1 space-y-1">
                                                    <input type="number" step="0.01" min="0" name="lines[{{ $lIdx }}][months][{{ $mIdx }}][debit_amount]" value="{{ $mRow['debit_amount'] ?? 0 }}" placeholder="Déb $" class="w-full rounded border border-slate-200 px-1.5 py-1 text-[11px]">
                                                    <input type="number" step="0.01" min="0" name="lines[{{ $lIdx }}][months][{{ $mIdx }}][credit_amount]" value="{{ $mRow['credit_amount'] ?? 0 }}" placeholder="Cré $" class="w-full rounded border border-slate-200 px-1.5 py-1 text-[11px]">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Totales y Balance -->
                <div class="mt-6 rounded-xl bg-slate-50 p-4 border border-slate-200">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="space-y-1">
                            <span class="text-xs font-semibold uppercase text-slate-500">Resumen de Balance</span>
                            <div class="flex items-center gap-6 text-sm">
                                <div>Total Débitos: <strong id="totalDebitsLabel" class="text-slate-900">$0.00</strong></div>
                                <div>Total Créditos: <strong id="totalCreditsLabel" class="text-slate-900">$0.00</strong></div>
                            </div>
                        </div>
                        <div id="balanceBadge" class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold bg-emerald-100 text-emerald-800">
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            <span>Líneas Balanceadas</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('modifications.show', $request) }}" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Cancelar
                </a>
                <button type="submit" class="rounded-lg bg-institutional-700 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-institutional-800 focus:outline-none focus:ring-2 focus:ring-institutional-600">
                    Guardar Correcciones
                </button>
            </div>
        </form>
    </div>

    <!-- Template for JS Line Rows -->
    <template id="lineTemplate">
        <div class="line-row rounded-xl border border-slate-200 bg-slate-50/50 p-4 space-y-4" data-index="__INDEX__">
            <div class="flex items-center justify-between gap-2 border-b border-slate-200 pb-2">
                <span class="text-xs font-bold text-institutional-700 uppercase tracking-wide">Línea #<span class="line-number">__NUMBER__</span></span>
                <button type="button" class="btn-remove-line text-xs font-semibold text-rose-600 hover:text-rose-800">
                    Eliminar Línea
                </button>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600">Actividad Origen (Débito)</label>
                    <select name="lines[__INDEX__][source_activity_id]" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs focus:border-institutional-600 focus:outline-none">
                        <option value="">-- Sin actividad origen --</option>
                        @foreach($activities as $act)
                            <option value="{{ $act->id }}">{{ $act->number }} - {{ Str::limit($act->description, 40) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600">Actividad Destino (Crédito)</label>
                    <select name="lines[__INDEX__][destination_activity_id]" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs focus:border-institutional-600 focus:outline-none">
                        <option value="">-- Sin actividad destino --</option>
                        @foreach($activities as $act)
                            <option value="{{ $act->id }}">{{ $act->number }} - {{ Str::limit($act->description, 40) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600">Monto Débito ($) *</label>
                    <input type="number" step="0.01" min="0" name="lines[__INDEX__][debit_amount]" value="0.00" required class="input-debit mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-800 focus:border-institutional-600 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600">Monto Crédito ($) *</label>
                    <input type="number" step="0.01" min="0" name="lines[__INDEX__][credit_amount]" value="0.00" required class="input-credit mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-800 focus:border-institutional-600 focus:outline-none">
                </div>

                <div class="sm:col-span-2 lg:col-span-4">
                    <label class="block text-xs font-medium text-slate-600">Descripción / Concepto del Movimiento *</label>
                    <input type="text" name="lines[__INDEX__][description]" required class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs focus:border-institutional-600 focus:outline-none" placeholder="Explicación del traspaso presupuestario o meta...">
                </div>
            </div>

            <!-- Mensualización Accordion -->
            <div class="border-t border-slate-200 pt-3">
                <button type="button" class="btn-toggle-months inline-flex items-center gap-1 text-xs font-semibold text-institutional-600 hover:text-institutional-800">
                    <svg class="size-3.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                    </svg>
                    <span>Configurar Mensualización (12 meses)</span>
                </button>

                <div class="months-grid mt-3 hidden rounded-lg border border-slate-200 bg-white p-3">
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 lg:grid-cols-6 text-xs">
                        @php
                            $monthNames = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                        @endphp
                        @foreach($monthNames as $mIdx => $mName)
                            <div class="rounded border border-slate-100 bg-slate-50/70 p-2">
                                <span class="font-bold text-slate-700">{{ $mIdx + 1 }}. {{ $mName }}</span>
                                <input type="hidden" name="lines[__INDEX__][months][{{ $mIdx }}][month]" value="{{ $mIdx + 1 }}">
                                <div class="mt-1 space-y-1">
                                    <input type="number" step="0.01" min="0" name="lines[__INDEX__][months][{{ $mIdx }}][debit_amount]" value="0" placeholder="Déb $" class="w-full rounded border border-slate-200 px-1.5 py-1 text-[11px]">
                                    <input type="number" step="0.01" min="0" name="lines[__INDEX__][months][{{ $mIdx }}][credit_amount]" value="0" placeholder="Cré $" class="w-full rounded border border-slate-200 px-1.5 py-1 text-[11px]">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </template>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let lineIndex = {{ count($lines) }};
            const container = document.getElementById('linesContainer');
            const template = document.getElementById('lineTemplate').innerHTML;
            const totalDebitsLabel = document.getElementById('totalDebitsLabel');
            const totalCreditsLabel = document.getElementById('totalCreditsLabel');
            const balanceBadge = document.getElementById('balanceBadge');
            const typeSelect = document.getElementById('type');

            function bindRowEvents(row) {
                row.querySelector('.btn-remove-line').addEventListener('click', function () {
                    if (container.querySelectorAll('.line-row').length > 1) {
                        row.remove();
                        renumberLines();
                        calculateTotals();
                    } else {
                        alert('Debe existir al menos una línea de modificación.');
                    }
                });

                row.querySelector('.btn-toggle-months').addEventListener('click', function () {
                    const grid = row.querySelector('.months-grid');
                    grid.classList.toggle('hidden');
                });

                row.querySelector('.input-debit').addEventListener('input', calculateTotals);
                row.querySelector('.input-credit').addEventListener('input', calculateTotals);
            }

            container.querySelectorAll('.line-row').forEach(row => bindRowEvents(row));

            function addLine() {
                const html = template
                    .replaceAll('__INDEX__', lineIndex)
                    .replaceAll('__NUMBER__', lineIndex + 1);

                const div = document.createElement('div');
                div.innerHTML = html;
                const row = div.firstElementChild;
                container.appendChild(row);
                bindRowEvents(row);

                lineIndex++;
                renumberLines();
                calculateTotals();
            }

            function renumberLines() {
                container.querySelectorAll('.line-row').forEach((row, idx) => {
                    row.querySelector('.line-number').textContent = idx + 1;
                });
            }

            function calculateTotals() {
                let debits = 0;
                let credits = 0;

                container.querySelectorAll('.input-debit').forEach(input => {
                    debits += parseFloat(input.value) || 0;
                });

                container.querySelectorAll('.input-credit').forEach(input => {
                    credits += parseFloat(input.value) || 0;
                });

                totalDebitsLabel.textContent = '$' + debits.toFixed(2);
                totalCreditsLabel.textContent = '$' + credits.toFixed(2);

                const isBalanced = Math.abs(debits - credits) < 0.005;
                const type = typeSelect.value;
                const requiresBalance = (type === 'SIN_AFECTACION' || type === 'REPROGRAMACION');

                if (!requiresBalance || isBalanced) {
                    balanceBadge.className = 'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold bg-emerald-100 text-emerald-800';
                    balanceBadge.innerHTML = '<svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg><span>' + (requiresBalance ? 'Líneas Balanceadas' : 'Válido') + '</span>';
                } else {
                    balanceBadge.className = 'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold bg-amber-100 text-amber-800';
                    balanceBadge.innerHTML = '<svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg><span>Débito y Crédito no coinciden (Diferencia: $' + Math.abs(debits - credits).toFixed(2) + ')</span>';
                }
            }

            typeSelect.addEventListener('change', calculateTotals);
            document.getElementById('btnAddLine').addEventListener('click', () => addLine());

            calculateTotals();
        });
    </script>
</x-layouts.app>
