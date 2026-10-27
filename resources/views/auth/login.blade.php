<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ingresar · SIGPOA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
    <main class="grid min-h-screen lg:grid-cols-2">
        <section class="hidden bg-institutional-800 p-12 text-white lg:flex lg:flex-col lg:justify-between">
            <div class="flex items-center gap-3"><span class="grid size-12 place-items-center rounded-xl bg-white font-bold text-institutional-800">DG</span><span class="text-xl font-semibold">DGAC Ecuador</span></div>
            <div class="max-w-xl"><p class="text-sm font-semibold uppercase tracking-[0.2em] text-sky-200">Planificación institucional</p><h1 class="mt-4 text-5xl font-bold leading-tight">Gestión trazable del Plan Operativo Anual</h1><p class="mt-5 text-lg text-sky-100">Una plataforma segura para planificar, aprobar y dar seguimiento al POA.</p></div>
            <p class="text-sm text-sky-200">Dirección General de Aviación Civil</p>
        </section>
        <section class="flex items-center justify-center p-6 sm:p-10">
            <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-7 shadow-xl sm:p-10">
                <div class="lg:hidden"><p class="font-bold text-institutional-700">DGAC · SIGPOA</p></div>
                <h2 class="mt-5 text-3xl font-bold">Bienvenido</h2><p class="mt-2 text-slate-600">Ingrese con sus credenciales institucionales.</p>
                <form method="POST" action="{{ route('login') }}" class="mt-8 grid gap-5">@csrf
                    <div class="grid gap-2"><label for="email" class="text-sm font-semibold">Correo electrónico</label><input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="rounded-lg border border-slate-300 px-4 py-3 focus:border-institutional-600 focus:outline-none focus:ring-2 focus:ring-institutional-100">@error('email')<p class="text-sm text-red-700">{{ $message }}</p>@enderror</div>
                    <div class="grid gap-2"><label for="password" class="text-sm font-semibold">Contraseña</label><input id="password" name="password" type="password" required autocomplete="current-password" class="rounded-lg border border-slate-300 px-4 py-3 focus:border-institutional-600 focus:outline-none focus:ring-2 focus:ring-institutional-100">@error('password')<p class="text-sm text-red-700">{{ $message }}</p>@enderror</div>
                    <label class="flex items-center gap-3 text-sm"><input type="checkbox" name="remember" value="1" class="size-4 rounded border-slate-300 text-institutional-600">Mantener sesión iniciada</label>
                    <button class="rounded-lg bg-institutional-700 px-4 py-3 font-semibold text-white hover:bg-institutional-800 focus:outline-none focus:ring-2 focus:ring-institutional-600 focus:ring-offset-2">Ingresar</button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
