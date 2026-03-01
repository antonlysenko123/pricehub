@extends('layouts.app')

@section('title', 'Постачальники · Price Hub')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div>
            <h1 class="text-lg font-semibold tracking-tight">Постачальники</h1>
            <p class="text-xs text-slate-500">
                Керування джерелами прайс-листів та налаштуванням імпорту.
            </p>
        </div>

        <a href="{{ route('suppliers.create') }}"
           class="inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-sky-600 text-white text-xs font-medium shadow-sm hover:bg-sky-700">
            <span class="text-base leading-none">+</span>
            <span>Додати постачальника</span>
        </a>
    </div>

    @if($suppliers->isEmpty())
        <div class="mt-4 bg-white border border-dashed border-slate-200 rounded-xl p-6 text-sm text-slate-500">
            Ще немає жодного постачальника. Натисни
            <span class="font-semibold">«Додати постачальника»</span>, щоб створити першого.
        </div>
    @else
        <div class="mt-4 bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="max-h-[70vh] overflow-auto scrollbar-thin">
                <table class="min-w-full text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 sticky top-0 z-10">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold text-slate-500">ID</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-500">Назва</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-500">Код</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-500">Тип</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-500">URL</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-500">Дії</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    @foreach($suppliers as $supplier)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-3 py-2 text-slate-500">{{ $supplier->id }}</td>
                            <td class="px-3 py-2 text-slate-800 font-medium">{{ $supplier->name }}</td>
                            <td class="px-3 py-2 font-mono text-[11px] text-slate-600">{{ $supplier->code }}</td>
                            <td class="px-3 py-2 text-[11px] uppercase text-slate-500">{{ $supplier->type }}</td>
                            <td class="px-3 py-2">
                                @if($supplier->source_url)
                                    <a href="{{ $supplier->source_url }}" target="_blank"
                                       class="max-w-xs inline-flex items-center text-[11px] text-sky-600 hover:underline truncate">
                                        {{ $supplier->source_url }}
                                    </a>
                                @else
                                    <span class="text-[11px] text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-2">
                                <div class="flex flex-wrap gap-2">
                                    <form action="{{ route('suppliers.fetch', $supplier) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                                class="px-3 py-1 rounded-full bg-emerald-600 text-white text-[11px] hover:bg-emerald-700">
                                            Завантажити
                                        </button>
                                    </form>

                                    <a href="{{ route('suppliers.preview', $supplier) }}"
                                       class="px-3 py-1 rounded-full bg-amber-500 text-white text-[11px] hover:bg-amber-600">
                                        Мапа колонок
                                    </a>

                                    <form action="{{ route('suppliers.import', $supplier) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                                class="px-3 py-1 rounded-full bg-indigo-600 text-white text-[11px] hover:bg-indigo-700">
                                            Імпорт
                                        </button>
                                    </form>

                                    <a href="{{ route('suppliers.edit', $supplier) }}"
                                       class="px-3 py-1 rounded-full bg-slate-600 text-white text-[11px] hover:bg-slate-700">
                                        Редагувати
                                    </a>

                                    <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST"
                                          onsubmit="return confirm('Видалити постачальника {{ $supplier->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="px-3 py-1 rounded-full bg-rose-600 text-white text-[11px] hover:bg-rose-700">
                                            Видалити
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
