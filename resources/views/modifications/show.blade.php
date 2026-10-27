<x-layouts.app title="Solicitud de Modificación: {{ $request->number }}">
    <div class="max-w-5xl space-y-6">
        <!-- Breadcrumb & Header -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="{{ route('modifications.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-institutional-700 hover:text-institutional-800">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Volver a Modificaciones
                </a>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ $request->number }}</h1>
                    @php
                        $badgeClasses = match($request->status->value) {
                            'APROBADA' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'BORRADOR' => 'bg-slate-100 text-slate-800 border-slate-200',
                            'DEVUELTA' => 'bg-amber-100 text-amber-800 border-amber-200',
                            'NEGADA' => 'bg-rose-100 text-rose-800 border-rose-200',
                            'VIABLE', 'AUTORIZADA' => 'bg-sky-100 text-sky-800 border-sky-200',
                            'REFORMA_PENDIENTE' => 'bg-purple-100 text-purple-800 border-purple-200',
                            default => 'bg-blue-100 text-blue-800 border-blue-200',
                        };
                    @endphp
                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-bold {{ $badgeClasses }}">
                        {{ $request->status->value }}
                    </span>
                    <span class="text-xs text-slate-400">Versión: v{{ $request->version }}</span>
                </div>
            </div>

            @can('modification.create')
                @if(in_array($request->status->value, ['BORRADOR', 'DEVUELTA']))
                    <a href="{{ route('modifications.edit', $request) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-institutional-600">
                        <svg class="size-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                        Corregir Solicitud
                    </a>
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

        <!-- Ficha de Metadatos -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <span class="text-xs font-semibold uppercase text-slate-500">Tipo de Modificación</span>
                <p class="mt-1 font-bold text-slate-900 text-sm">{{ $request->type->value }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <span class="text-xs font-semibold uppercase text-slate-500">Unidad Solicitante</span>
                <p class="mt-1 font-bold text-slate-900 text-sm">{{ $request->organizationalUnit?->name ?? 'N/A' }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <span class="text-xs font-semibold uppercase text-slate-500">Proceso POA / Ejercicio</span>
                <p class="mt-1 font-bold text-slate-900 text-sm">{{ $request->process?->phase?->value }} ({{ $request->process?->planningPeriod?->year }})</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <span class="text-xs font-semibold uppercase text-slate-500">Solicitado Por</span>
                <p class="mt-1 font-bold text-slate-900 text-sm">{{ $request->requester?->name ?? 'N/A' }}</p>
            </div>
        </div>

        <!-- Justificación -->
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Justificación Técnica / Motivación</h2>
            <p class="mt-2 text-sm text-slate-800 leading-relaxed whitespace-pre-line">{{ $request->justification }}</p>
        </div>

        <!-- Tabla de Líneas y Mensualización -->
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Líneas de Modificación ({{ $request->lines->count() }})</h2>
                    <p class="text-xs text-slate-500">Movimientos presupuestarios entre actividades y metas operativas.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase text-slate-500">
                        <tr>
                            <th class="px-6 py-3.5">#</th>
                            <th class="px-6 py-3.5">Actividad Origen (Débito)</th>
                            <th class="px-6 py-3.5">Actividad Destino (Crédito)</th>
                            <th class="px-6 py-3.5 text-right">Débito ($)</th>
                            <th class="px-6 py-3.5 text-right">Crédito ($)</th>
                            <th class="px-6 py-3.5">Concepto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @php
                            $totalDebits = 0;
                            $totalCredits = 0;
                        @endphp
                        @foreach($request->lines as $idx => $line)
                            @php
                                $totalDebits += (float) $line->debit_amount;
                                $totalCredits += (float) $line->credit_amount;
                            @endphp
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-6 py-4 text-xs font-bold text-slate-400">{{ $idx + 1 }}</td>
                                <td class="px-6 py-4">
                                    @if($line->sourceActivity)
                                        <span class="font-semibold text-slate-900">{{ $line->sourceActivity->number }}</span>
                                        <span class="block text-xs text-slate-500">{{ Str::limit($line->sourceActivity->description, 45) }}</span>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Sin actividad origen</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($line->destinationActivity)
                                        <span class="font-semibold text-slate-900">{{ $line->destinationActivity->number }}</span>
                                        <span class="block text-xs text-slate-500">{{ Str::limit($line->destinationActivity->description, 45) }}</span>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Sin actividad destino</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-mono font-semibold text-slate-900">
                                    ${{ number_format($line->debit_amount, 2) }}
                                </td>
                                <td class="px-6 py-4 text-right font-mono font-semibold text-slate-900">
                                    ${{ number_format($line->credit_amount, 2) }}
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-700">
                                    {{ $line->description }}
                                </td>
                            </tr>
                            @if($line->months->isNotEmpty())
                                <tr class="bg-slate-50/40">
                                    <td colspan="6" class="px-6 py-3">
                                        <div class="rounded-lg border border-slate-200 bg-white p-3">
                                            <span class="text-[11px] font-bold uppercase text-slate-500">Distribución Mensual Línea #{{ $idx + 1 }}:</span>
                                            <div class="mt-2 grid grid-cols-4 sm:grid-cols-6 lg:grid-cols-12 gap-2 text-center text-xs">
                                                @php
                                                    $mNames = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                                                @endphp
                                                @foreach($line->months->sortBy('month') as $m)
                                                    <div class="rounded border border-slate-100 bg-slate-50 p-1.5">
                                                        <span class="block font-bold text-slate-700 text-[10px]">{{ $mNames[$m->month - 1] ?? $m->month }}</span>
                                                        <span class="block text-[11px] text-rose-600 font-mono">-{{ number_format($m->debit_amount, 2) }}</span>
                                                        <span class="block text-[11px] text-emerald-600 font-mono">+{{ number_format($m->credit_amount, 2) }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50 font-semibold text-slate-900 border-t border-slate-200">
                        <tr>
                            <td colspan="3" class="px-6 py-3.5 text-right uppercase text-xs text-slate-500">Totales:</td>
                            <td class="px-6 py-3.5 text-right font-mono">${{ number_format($totalDebits, 2) }}</td>
                            <td class="px-6 py-3.5 text-right font-mono">${{ number_format($totalCredits, 2) }}</td>
                            <td class="px-6 py-3.5 text-xs text-slate-500">
                                @if(in_array($request->type->value, ['SIN_AFECTACION', 'REPROGRAMACION']))
                                    @if(abs($totalDebits - $totalCredits) < 0.005)
                                        <span class="text-emerald-700 font-bold">✓ Balanceado</span>
                                    @else
                                        <span class="text-rose-700 font-bold">⚠ Desbalanceado</span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Documentos Habilitantes -->
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Documentos Habilitantes ({{ $request->documents->count() }})</h2>
                    <p class="text-xs text-slate-500">Expediente digital, dictámenes, matrices y resoluciones firmadas.</p>
                </div>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($request->documents as $doc)
                    <div class="flex flex-wrap items-center justify-between gap-4 py-3 text-sm">
                        <div class="flex items-center gap-3">
                            <div class="grid size-9 place-items-center rounded-lg bg-institutional-50 text-institutional-700">
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                            </div>
                            <div>
                                <strong class="text-slate-900">{{ $doc->original_name }}</strong>
                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                    <span class="rounded bg-slate-100 px-1.5 py-0.5 font-medium">{{ $doc->document_type }}</span>
                                    <span>v{{ $doc->version }}</span>
                                    <span>· Subido por {{ $doc->uploader?->name ?? 'Usuario' }} el {{ $doc->uploaded_at?->format('d/m/Y H:i') }}</span>
                                </div>
                                <p class="text-[10px] font-mono text-slate-400 mt-0.5">SHA-256: {{ $doc->checksum }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="py-4 text-center text-xs text-slate-400 italic">No hay documentos adjuntos en este expediente.</p>
                @endforelse
            </div>

            @can('modification.create')
                <div class="mt-4 rounded-lg border border-dashed border-slate-300 bg-slate-50/50 p-4">
                    <h3 class="text-xs font-bold uppercase text-slate-700">Adjuntar Nuevo Documento Habilitante</h3>
                    <form method="POST" action="{{ route('modifications.documents.store', $request) }}" enctype="multipart/form-data" class="mt-3 grid gap-3 sm:grid-cols-3">
                        @csrf
                        <div>
                            <label for="doc_type" class="block text-xs font-medium text-slate-600">Tipo Documental *</label>
                            <input type="text" name="document_type" id="doc_type" required value="INFORME_TECNICO" class="mt-1 w-full rounded-lg border border-slate-300 bg-white p-2 text-xs focus:border-institutional-600 focus:outline-none">
                        </div>
                        <div>
                            <label for="doc_file" class="block text-xs font-medium text-slate-600">Archivo PDF (máx 10MB) *</label>
                            <input type="file" name="document" id="doc_file" required accept="application/pdf" class="mt-1 w-full rounded-lg border border-slate-300 bg-white p-1 text-xs">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full rounded-lg bg-institutional-700 px-4 py-2 text-xs font-semibold text-white hover:bg-institutional-800">
                                Subir Documento
                            </button>
                        </div>
                    </form>
                </div>
            @endcan
        </div>

        <!-- Panel de Informe de Viabilidad Técnica -->
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            <h2 class="text-base font-semibold text-slate-900 border-b border-slate-100 pb-3">Informe de Viabilidad DPGE</h2>
            @if($request->result && $request->result->viability_report)
                <div class="rounded-lg bg-slate-50 p-4 border border-slate-200">
                    <div class="flex items-center justify-between text-xs text-slate-500 border-b border-slate-200 pb-2 mb-2">
                        <span><strong>Emitido por:</strong> {{ $request->result->approver?->name ?? 'DPGE' }}</span>
                        <span><strong>Fecha:</strong> {{ $request->result->approved_at?->format('d/m/Y H:i') }}</span>
                    </div>
                    <p class="text-sm text-slate-800 whitespace-pre-line">{{ $request->result->viability_report }}</p>
                </div>
            @else
                <p class="text-xs text-slate-400 italic">No se ha registrado informe de viabilidad técnica formal.</p>
            @endif

            @can('modification.review')
                <div class="mt-3">
                    <h3 class="text-xs font-bold uppercase text-slate-700">Registrar / Actualizar Viabilidad DPGE</h3>
                    <form method="POST" action="{{ route('modifications.viability', $request) }}" class="mt-2 space-y-3">
                        @csrf
                        <textarea name="viability_report" rows="3" required class="w-full rounded-lg border border-slate-300 p-3 text-xs focus:border-institutional-600 focus:outline-none" placeholder="Texto del dictamen de viabilidad técnica y análisis de impacto presupuestario...">{{ old('viability_report', $request->result?->viability_report) }}</textarea>
                        <div class="flex justify-end">
                            <button type="submit" class="rounded-lg bg-institutional-700 px-4 py-2 text-xs font-semibold text-white hover:bg-institutional-800">
                                Guardar Informe de Viabilidad
                            </button>
                        </div>
                    </form>
                </div>
            @endcan
        </div>

        <!-- Panel de Reforma eSIGEF y CUR (si aplica) -->
        @if($request->requires_esigef)
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Control de Reforma Presupuestaria eSIGEF</h2>
                        <p class="text-xs text-slate-500">Registro manual auditable de la reforma y CUR generados en el eSIGEF.</p>
                    </div>
                    @if($request->esigef_reform_number && $request->reform_cur)
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">
                            ✓ Registrado en eSIGEF
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                            ⚠ Pendiente de Registro
                        </span>
                    @endif
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-lg bg-slate-50 p-3 border border-slate-200">
                        <span class="text-xs font-semibold uppercase text-slate-500">Número de Reforma eSIGEF:</span>
                        <p class="mt-1 font-mono font-bold text-slate-900">{{ $request->esigef_reform_number ?? 'No registrado' }}</p>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-3 border border-slate-200">
                        <span class="text-xs font-semibold uppercase text-slate-500">CUR de Reforma:</span>
                        <p class="mt-1 font-mono font-bold text-slate-900">{{ $request->reform_cur ?? 'No registrado' }}</p>
                    </div>
                </div>

                @canany(['modification.verify_budget', 'modification.review'])
                    <div class="mt-3 rounded-lg border border-slate-200 bg-slate-50/50 p-4">
                        <h3 class="text-xs font-bold uppercase text-slate-700">Actualizar Datos eSIGEF (Financiero / DPGE)</h3>
                        <form method="POST" action="{{ route('modifications.esigef', $request) }}" class="mt-2 grid gap-3 sm:grid-cols-3">
                            @csrf
                            <div>
                                <label for="esigef_reform_number" class="block text-xs font-medium text-slate-600">Nro. Reforma eSIGEF *</label>
                                <input type="text" name="esigef_reform_number" id="esigef_reform_number" value="{{ old('esigef_reform_number', $request->esigef_reform_number) }}" required class="mt-1 w-full rounded-lg border border-slate-300 bg-white p-2 text-xs focus:border-institutional-600 focus:outline-none">
                            </div>
                            <div>
                                <label for="reform_cur" class="block text-xs font-medium text-slate-600">Nro. CUR de Reforma *</label>
                                <input type="text" name="reform_cur" id="reform_cur" value="{{ old('reform_cur', $request->reform_cur) }}" required class="mt-1 w-full rounded-lg border border-slate-300 bg-white p-2 text-xs focus:border-institutional-600 focus:outline-none">
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="w-full rounded-lg bg-institutional-700 px-4 py-2 text-xs font-semibold text-white hover:bg-institutional-800">
                                    Guardar Reforma eSIGEF
                                </button>
                            </div>
                        </form>
                    </div>
                @endcanany
            </div>
        @endif

        <!-- Panel de Transición de Estados (Workflow) -->
        @can('modification.review')
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                <h2 class="text-base font-semibold text-slate-900 border-b border-slate-100 pb-3">Acciones de Workflow / Transición de Estado</h2>
                @if(count($statuses))
                    <form method="POST" action="{{ route('modifications.transition', $request) }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="version" value="{{ $request->version }}">

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="target_status" class="block text-xs font-semibold text-slate-700 uppercase">Siguiente Estado *</label>
                                <select name="status" id="target_status" required class="mt-1.5 w-full rounded-lg border border-slate-300 p-2.5 text-sm focus:border-institutional-600 focus:outline-none font-semibold">
                                    @foreach($statuses as $s)
                                        <option value="{{ $s->value }}">{{ $s->value }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="transition_comment" class="block text-xs font-semibold text-slate-700 uppercase">Motivo / Observación / Dictamen de la Transición *</label>
                                <textarea name="comment" id="transition_comment" rows="2" required class="mt-1.5 w-full rounded-lg border border-slate-300 p-3 text-sm focus:border-institutional-600 focus:outline-none" placeholder="Detalle la motivación o fundamento de este cambio de estado..."></textarea>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="rounded-lg bg-institutional-700 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-institutional-800 focus:outline-none focus:ring-2 focus:ring-institutional-600">
                                Ejecutar Transición
                            </button>
                        </div>
                    </form>
                @else
                    <div class="rounded-lg bg-slate-50 p-4 text-center text-xs text-slate-500">
                        La solicitud se encuentra en el estado terminal <strong>{{ $request->status->value }}</strong>. No hay transiciones automáticas posteriores disponibles.
                    </div>
                @endif
            </div>
        @endcan

        <!-- Historial de Transiciones y Trazabilidad -->
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            <h2 class="text-base font-semibold text-slate-900 border-b border-slate-100 pb-3">Trazabilidad y Auditoría de Estados</h2>
            <div class="relative pl-6 space-y-6 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                @forelse($request->workflowTransitions->sortByDesc('created_at') as $trans)
                    <div class="relative">
                        <div class="absolute -left-6 top-1 size-2.5 rounded-full bg-institutional-600 ring-4 ring-white"></div>
                        <div class="text-xs">
                            <span class="font-bold text-slate-900">{{ $trans->from_status }} → {{ $trans->to_status }}</span>
                            <span class="text-slate-500">por {{ $trans->user?->name ?? 'Usuario' }} el {{ $trans->created_at?->format('d/m/Y H:i') }}</span>
                            <p class="mt-1 text-slate-700 italic">"{{ $trans->comment }}"</p>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 italic">No hay transiciones registradas aún.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.app>
