<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Price Hub')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* трохи дрібної косметики */
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        .scrollbar-thin::-webkit-scrollbar {
            height: 6px;
            width: 6px;
        }
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background-color: rgba(148, 163, 184, 0.8);
            border-radius: 9999px;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800">
<div class="min-h-screen flex flex-col">

    {{-- Top bar --}}
    <header class="border-b bg-white/80 backdrop-blur">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-14">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-sky-600 flex items-center justify-center text-white text-sm font-semibold">
                    PH
                </div>
                <div>
                    <div class="text-sm font-semibold tracking-tight">Price Hub</div>
                    <div class="text-[11px] text-slate-500">Централізований хаб прайс-листів</div>
                </div>
            </div>

            <nav class="flex items-center gap-4 text-xs text-slate-600">
                <a href="{{ route('suppliers.index') }}"
                   class="px-3 py-1 rounded-full hover:bg-slate-100 @if(request()->is('suppliers*')) text-sky-600 font-semibold @endif">
                    Suppliers
                </a>
                {{-- на майбутнє: Price files, Matrix, Reports --}}
            </nav>
        </div>
    </header>

    {{-- Main content --}}
    <main class="flex-1">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-4">

            {{-- Flash message --}}
            @if(session('status'))
                <div class="border border-emerald-200 bg-emerald-50 text-emerald-800 text-xs px-3 py-2 rounded-lg flex items-start gap-2">
                    <span class="mt-[2px]">?</span>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            {{-- Validation errors --}}
            @if($errors->any())
                <div class="border border-rose-200 bg-rose-50 text-rose-800 text-xs px-3 py-2 rounded-lg">
                    <div class="font-semibold mb-1">Будь ласка, виправ помилки:</div>
                    <ul class="list-disc list-inside space-y-[2px]">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    {{-- Footer --}}
    <footer class="border-t bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between text-[11px] text-slate-400">
            <div>Price Hub · внутрішній інструмент</div>
            <div>v0.1 · {{ now()->format('Y') }}</div>
        </div>
    </footer>
</div>
</body>
</html>
