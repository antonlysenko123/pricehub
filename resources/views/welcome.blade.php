<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Price Hub')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script src="https://cdn.tailwindcss.com"></script>

</head>
<body class="bg-slate-100 text-slate-900">
<div class="min-h-screen flex flex-col">

    <header class="bg-sky-700 text-white py-4 mb-6 shadow">
        <div class="container mx-auto px-4 flex items-center justify-between">
            <h1 class="text-xl font-semibold">
                Price Hub
            </h1>
            <nav class="flex gap-4 text-sm">
                <a href="{{ route('suppliers.index') }}" class="hover:underline">
                    Постачальники
                </a>
            </nav>
        </div>
    </header>

    <main class="flex-1 container mx-auto px-4 pb-8">
        @if (session('status'))
            <div class="mb-4 p-3 bg-emerald-100 border border-emerald-300 text-emerald-900 text-sm rounded">
                {!! nl2br(e(session('status'))) !!}
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="border-t text-xs text-slate-500 py-3 mt-auto">
        <div class="container mx-auto px-4">
            &copy; {{ date('Y') }} Enko. Internal tool.
        </div>
    </footer>

</div>
</body>
</html>
