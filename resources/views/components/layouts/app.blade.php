<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SIGPOA' }} · DGAC</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-[17rem_1fr]">
        <aside class="bg-institutional-800 px-5 py-6 font-sans text-white">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-white">
                <span class="grid size-11 place-items-center rounded-xl bg-white font-bold text-institutional-800">DG</span>
                <span><strong class="block text-lg">SIGPOA</strong><span class="text-sm text-sky-100">DGAC Ecuador</span></span>
            </a>
            <nav class="mt-8" aria-label="Navegación principal">
                <a href="{{ route('dashboard') }}" aria-current="page" class="flex items-center gap-3 rounded-lg bg-white/15 px-4 py-3 font-medium">Resumen</a>
                @can('admin.parameters')
                    <a href="{{ route('poa-processes.index') }}" class="mt-2 flex items-center gap-3 rounded-lg px-4 py-3 font-medium text-sky-50 hover:bg-white/10">Control POA</a>
                @endcan
                @can('poa.view')<a href="{{ route('poa-activities.index') }}" class="mt-2 flex items-center gap-3 rounded-lg px-4 py-3 font-medium text-sky-50 hover:bg-white/10">POA necesidades</a>@endcan
                @can('poa.approve')<a href="{{ route('budget-ceilings.index') }}" class="mt-2 flex items-center gap-3 rounded-lg px-4 py-3 font-medium text-sky-50 hover:bg-white/10">Techos presupuestarios</a>@endcan
                @can('certification.create')<a href="{{ route('poa-certifications.index') }}" class="mt-2 flex items-center gap-3 rounded-lg px-4 py-3 font-medium text-sky-50 hover:bg-white/10">Certificaciones POA</a>@endcan
                @canany(['certification.create', 'execution.import', 'modification.verify_budget'])<a href="{{ route('budget-certifications.index') }}" class="mt-2 flex items-center gap-3 rounded-lg px-4 py-3 font-medium text-sky-50 hover:bg-white/10">Certif. Presupuestarias</a>@endcanany
                @can('modification.review')<a href="{{ route('modifications.index') }}" class="mt-2 flex items-center gap-3 rounded-lg px-4 py-3 font-medium text-sky-50 hover:bg-white/10">Modificaciones</a>@endcan
                @can('execution.import')<a href="{{ route('execution-batches.index') }}" class="mt-2 flex items-center gap-3 rounded-lg px-4 py-3 font-medium text-sky-50 hover:bg-white/10">Lotes eSIGEF</a>@endcan
                @can('execution.import')<a href="{{ route('budget-accruals.index') }}" class="mt-2 flex items-center gap-3 rounded-lg px-4 py-3 font-medium text-sky-50 hover:bg-white/10">Devengados / CUR</a>@endcan
                @can('execution.close')<a href="{{ route('execution-closings.index') }}" class="mt-2 flex items-center gap-3 rounded-lg px-4 py-3 font-medium text-sky-50 hover:bg-white/10">Cierres Mensuales</a>@endcan
                @can('reports.view')<a href="{{ route('reports.index') }}" class="mt-2 flex items-center gap-3 rounded-lg px-4 py-3 font-medium text-sky-50 hover:bg-white/10">Reportes y BI</a>@endcan
                @can('admin.parameters')<a href="{{ route('migration.reconciliation') }}" class="mt-2 flex items-center gap-3 rounded-lg px-4 py-3 font-medium text-sky-50 hover:bg-white/10">Convivencia DB2</a>@endcan
            </nav>
        </aside>
        <div class="min-w-0">
            <header class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 bg-white px-5 py-4 shadow-sm sm:px-8">
                <div><p class="text-sm text-slate-500">Sistema Integrado de Gestión</p><p class="font-semibold">Plan Operativo Anual</p></div>
                <div class="flex items-center gap-4">
                    <div class="text-right"><p class="text-sm font-semibold">{{ auth()->user()->name }}</p><p class="text-xs text-slate-500">Usuario activo</p></div>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-institutional-600">Cerrar sesión</button></form>
                </div>
            </header>
            <main class="p-5 sm:p-8">{{ $slot }}</main>
        </div>
    </div>
</body>
</html>
