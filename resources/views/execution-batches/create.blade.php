<x-layouts.app title="Importar Lote de Ejecución eSIGEF">
    <div class="max-w-3xl space-y-6">
        <div>
            <a href="{{ route('execution-batches.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-institutional-700 hover:text-institutional-800">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Volver a Lotes de Ejecución
            </a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Importar Archivo CSV de CURs (eSIGEF)</h1>
            <p class="text-sm text-slate-500">Cargue el reporte detallado descargado desde eSIGEF. El sistema calculará el checksum SHA-256 para evitar duplicidades.</p>
        </div>

        @if($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                <p class="font-semibold">Error en la importación:</p>
                <ul class="mt-1 list-disc pl-5 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('execution-batches.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-5">
                <h2 class="text-base font-semibold text-slate-900 border-b border-slate-100 pb-3">Parámetros del Lote</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="year" class="block text-xs font-semibold uppercase text-slate-700">Año Fiscal *</label>
                        <input type="number" name="year" id="year" value="{{ old('year', date('Y')) }}" required min="2020" max="2100" class="mt-1.5 w-full rounded-lg border border-slate-300 p-2.5 text-sm focus:border-institutional-600 focus:outline-none">
                    </div>

                    <div>
                        <label for="month" class="block text-xs font-semibold uppercase text-slate-700">Mes de Ejecución (Opcional)</label>
                        <select name="month" id="month" class="mt-1.5 w-full rounded-lg border border-slate-300 p-2.5 text-sm focus:border-institutional-600 focus:outline-none">
                            <option value="">-- Deducir de las fechas del CSV --</option>
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ old('month') == $m ? 'selected' : '' }}>
                                    Mes {{ $m }} - {{ ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'][$m - 1] }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div>
                    <label for="file" class="block text-xs font-semibold uppercase text-slate-700">Archivo CSV de eSIGEF *</label>
                    <input type="file" name="file" id="file" required accept=".csv,.txt" class="mt-2 block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-institutional-50 file:text-institutional-700 hover:file:bg-institutional-100">
                    <p class="mt-2 text-xs text-slate-500">Formato esperado: CSV delimitado por comas, punto y coma o tabulación. Columnas recomendadas: <code>cur_number, cur_line, certification_number, activity_code, accrual_date, amount</code>.</p>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('execution-batches.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancelar</a>
                <button type="submit" class="rounded-lg bg-institutional-700 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-institutional-800 focus:outline-none focus:ring-2 focus:ring-institutional-600">
                    Subir y Validar Lote
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
