@extends('layouts.app')

@section('title', 'Suppliers · Price Hub')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div>
            <h1 class="text-lg font-semibold tracking-tight">Suppliers</h1>
            <p class="text-xs text-slate-500">
                Керування джерелами прайс-листів та налаштуванням імпорту.
            </p>
        </div>

        <a href="{{ route('suppliers.create') }}"
           class="inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-sky-600 text-white text-xs font-medium shadow-sm hover:bg-sky-700">
            <span class="text-base leading-none">+</span>
            <span>Add supplier</span>
        </a>
    </div>

    @if($suppliers->isEmpty())
        <div class="mt-4 bg-white border border-dashed border-slate-200 rounded-xl p-6 text-sm text-slate-500">
            Ще немає жодного постачальника. Додай першого через кнопку <span class="font-semibold">Add supplier</span>.
        </div>
    @else
        <div class="mt-4 bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="max-h-[70vh] overflow-auto scrollbar-thin">
                <table class="min-w-full text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 sticky top-0 z-10">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold text-slate-500">ID</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-500">Name</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-500">Code</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-500">Type</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-500">URL</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-500">Actions</th>
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
                                <a href="{{ $supplier->source_url }}" target="_blank"
                                   class="max-w-xs inline-flex items-center text-[11px] text-sky-600 hover:underline truncate">
                                    {{ $supplier->source_url }}
                                </a>
                            </td>
                            <td class="px-3 py-2">
                                <div class="flex flex-wrap gap-2">
                                    <form action="{{ route('suppliers.fetch', $supplier) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                                class="px-3 py-1 rounded-full bg-emerald-600 text-white text-[11px] hover:bg-emerald-700">
                                            Fetch
                                        </button>
                                    </form>

                                    <a href="{{ route('suppliers.preview', $supplier) }}"
                                       class="px-3 py-1 rounded-full bg-amber-500 text-white text-[11px] hover:bg-amber-600">
                                        Map
                                    </a>

                                    <form action="{{ route('suppliers.import', $supplier) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                                class="px-3 py-1 rounded-full bg-indigo-600 text-white text-[11px] hover:bg-indigo-700">
                                            Import
                                        </button>
                                    </form>

                                    <a href="{{ route('suppliers.edit', $supplier) }}"
                                       class="px-3 py-1 rounded-full bg-slate-600 text-white text-[11px] hover:bg-slate-700">
                                        Edit
                                    </a>

                                    <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST"
                                          onsubmit="return confirm('Delete supplier {{ $supplier->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="px-3 py-1 rounded-full bg-rose-600 text-white text-[11px] hover:bg-rose-700">
                                            Delete
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
