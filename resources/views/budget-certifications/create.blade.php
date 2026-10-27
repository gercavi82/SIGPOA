<x-layouts.app title="Nueva Certificación Presupuestaria">
    <div class="max-w-4xl space-y-6">
        <div>
            <a href="{{ route('budget-certifications.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-institutional-700 hover:text-institutional-800">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Volver a Certificaciones Presupuestarias
            </a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Registrar Certificación Presupuestaria (CERA01)</h1>
            <p class="text-sm text-slate-500">Ingrese los datos de la certificación presupuestaria y sus líneas de detalle (LINAR6).</p>
        </div>

        @if($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                <p class="font-semibold">Por favor corrija los siguientes errores:</p>
                <ul class="mt-1 list-disc pl-5 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('budget-certifications.store') }}" class="space-y-6">
            @csrf

            <!-- Cabecera -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                <h2 class="text-base font-semibold text-slate-900 border-b border-slate-100 pb-3">Información General</h2>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label for="certification_number" class="block text-xs font-semibold uppercase text-slate-700">Nro. Certificación eSIGEF *</label>
                        <input type="text" name="certification_number" id="certification_number" value="{{ old('certification_number') }}" required placeholder="CP-2026-001" class="mt-1.5 w-full rounded-lg border border-slate-300 p-2.5 text-sm focus:border-institutional-600 focus:outline-none">
                    </div>

                    <div>
                        <label for="certification_date" class="block text-xs font-semibold uppercase text-slate-700">Fecha de Emisión *</label>
                        <input type="date" name="certification_date" id="certification_date" value="{{ old('certification_date', date('Y-m-d')) }}" required class="mt-1.5 w-full rounded-lg border border-slate-300 p-2.5 text-sm focus:border-institutional-600 focus:outline-none">
                    </div>

                    <div>
                        <label for="year" class="block text-xs font-semibold uppercase text-slate-700">Ejercicio Fiscal (Año) *</label>
                        <input type="number" name="year" id="year" value="{{ old('year', date('Y')) }}" required min="2020" max="2100" class="mt-1.5 w-full rounded-lg border border-slate-300 p-2.5 text-sm focus:border-institutional-600 focus:outline-none">
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="poa_certification_id" class="block text-xs font-semibold uppercase text-slate-700">Vincular Certificación POA (Opcional)</label>
                        <select name="poa_certification_id" id="poa_certification_id" class="mt-1.5 w-full rounded-lg border border-slate-300 p-2.5 text-sm focus:border-institutional-600 focus:outline-none">
                            <option value="">-- Sin vinculación inicial --</option>
                            @foreach($poaCertifications as $pc)
                                <option value="{{ $pc->id }}" {{ old('poa_certification_id') == $pc->id ? 'selected' : '' }}>
                                    {{ $pc->number }} - ${{ number_format($pc->total_amount, 2) }} ({{ Str::limit($pc->subject, 40) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="funding_source" class="block text-xs font-semibold uppercase text-slate-700">Fuente de Financiamiento</label>
                        <input type="text" name="funding_source" id="funding_source" value="{{ old('funding_source', '001') }}" placeholder="001 / RECURSOS FISCALES" class="mt-1.5 w-full rounded-lg border border-slate-300 p-2.5 text-sm focus:border-institutional-600 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label for="concept" class="block text-xs font-semibold uppercase text-slate-700">Concepto / Objeto de la Certificación *</label>
                    <textarea name="concept" id="concept" rows="2" required class="mt-1.5 w-full rounded-lg border border-slate-300 p-2.5 text-sm focus:border-institutional-600 focus:outline-none" placeholder="Descripción del gasto o compromiso certificado...">{{ old('concept') }}</textarea>
                </div>

                <div>
                    <label for="total_amount" class="block text-xs font-semibold uppercase text-slate-700">Monto Total ($) *</label>
                    <input type="number" step="0.01" min="0.01" name="total_amount" id="total_amount" value="{{ old('total_amount') }}" required placeholder="0.00" class="mt-1.5 w-full rounded-lg border border-slate-300 p-2.5 text-sm font-bold font-mono focus:border-institutional-600 focus:outline-none">
                </div>
            </div>

            <!-- Líneas de Detalle (LINAR6) -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                <h2 class="text-base font-semibold text-slate-900 border-b border-slate-100 pb-3">Línea de Certificación Inicial (LINAR6)</h2>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="sm:col-span-2">
                        <label for="line_activity" class="block text-xs font-semibold uppercase text-slate-700">Actividad POA Asociada</label>
                        <select name="lines[0][poa_activity_id]" id="line_activity" class="mt-1.5 w-full rounded-lg border border-slate-300 p-2.5 text-sm focus:border-institutional-600 focus:outline-none">
                            <option value="">-- Seleccione Actividad POA --</option>
                            @foreach($activities as $act)
                                <option value="{{ $act->id }}" {{ old('lines.0.poa_activity_id') == $act->id ? 'selected' : '' }}>
                                    {{ $act->code ?? $act->number }} - {{ Str::limit($act->name ?? $act->description, 50) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="line_amount" class="block text-xs font-semibold uppercase text-slate-700">Monto de la Línea ($) *</label>
                        <input type="number" step="0.01" min="0.01" name="lines[0][amount]" id="line_amount" value="{{ old('lines.0.amount') }}" required placeholder="0.00" class="mt-1.5 w-full rounded-lg border border-slate-300 p-2.5 text-sm font-mono focus:border-institutional-600 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label for="line_concept" class="block text-xs font-semibold uppercase text-slate-700">Concepto de la Línea</label>
                    <input type="text" name="lines[0][concept]" id="line_concept" value="{{ old('lines.0.concept') }}" placeholder="Detalle específico del ítem..." class="mt-1.5 w-full rounded-lg border border-slate-300 p-2.5 text-sm focus:border-institutional-600 focus:outline-none">
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('budget-certifications.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancelar</a>
                <button type="submit" class="rounded-lg bg-institutional-700 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-institutional-800 focus:outline-none focus:ring-2 focus:ring-institutional-600">
                    Guardar Certificación Presupuestaria
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
