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
            <span class="text-base leading-none">＋</span>
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
            <div class="max-h-[75vh] overflow-auto scrollbar-thin">
                <table class="min-w-full text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 sticky top-0 z-10">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold text-slate-500">ID</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-500">Назва</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-500">Код</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-500">Тип</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-500">URL</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-500">Прогрес</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-500">Дії</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    @foreach($suppliers as $supplier)
                        @php $pf = $supplier->latestPriceFile; @endphp
                        <tr class="hover:bg-slate-50/70" data-supplier-id="{{ $supplier->id }}">
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

                            {{-- Прогрес --}}
                            <td class="px-3 py-2">
                                <div class="space-y-1">
                                    <div class="h-2 w-40 bg-slate-200 rounded-full overflow-hidden">
                                        <div class="h-2 js-progress-bar transition-all duration-500"
                                             style="width: {{ $pf->progress ?? 0 }}%;
                                                 @if($pf && $pf->status === 'imported') background-color: #22c55e;
                                                 @elseif($pf && $pf->status === 'failed') background-color: #ef4444;
                                                 @else background-color: #0ea5e9;
                                                 @endif">
                                        </div>
                                    </div>
                                    <div class="text-[10px] text-slate-500 js-progress-label">
                                        @if($pf)
                                            {{ $pf->current_action ?? $pf->status }} ({{ $pf->progress ?? 0 }}%)
                                            @if($pf->rows_count)
                                                · {{ number_format($pf->rows_count, 0, ',', ' ') }} рядків
                                            @endif
                                            @if($pf->updated_at)
                                                · {{ $pf->updated_at->format('d.m.Y H:i') }}
                                            @endif
                                        @else
                                            Немає прайсу
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Дії --}}
                            <td class="px-3 py-2">
                                <div class="flex flex-wrap gap-2">
                                    <form action="{{ route('suppliers.sync', $supplier) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                                class="px-3 py-1 rounded-full bg-emerald-600 text-white text-[11px] hover:bg-emerald-700">
                                            Оновити прайс
                                        </button>
                                    </form>

                                    <a href="{{ route('suppliers.preview', $supplier) }}"
                                       class="px-3 py-1 rounded-full bg-slate-100 text-[11px] text-slate-700 hover:bg-slate-200">
                                        Відкрити прайс
                                    </a>

                                    <a href="{{ route('suppliers.edit', $supplier) }}"
                                       class="px-3 py-1 rounded-full bg-slate-600 text-white text-[11px] hover:bg-slate-700">
                                        Налаштування
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

@section('scripts')
    <script>
        function updateStatuses() {
            fetch('{{ route('suppliers.statuses') }}')
                .then(response => response.json())
                .then(data => {
                    data.forEach(item => {
                        const row = document.querySelector('[data-supplier-id="' + item.id + '"]');
                        if (!row) return;

                        const bar   = row.querySelector('.js-progress-bar');
                        const label = row.querySelector('.js-progress-label');
                        if (!bar || !label) return;

                        if (!item.status) {
                            bar.style.width = '0%';
                            bar.style.backgroundColor = '#cbd5f5';
                            label.textContent = 'Немає прайсу';
                            return;
                        }

                        const p = item.progress || 0;
                        bar.style.width = p + '%';

                        if (item.status === 'imported') {
                            bar.style.backgroundColor = '#22c55e';
                        } else if (item.status === 'failed') {
                            bar.style.backgroundColor = '#ef4444';
                        } else {
                            bar.style.backgroundColor = '#0ea5e9';
                        }

                        let text = (item.currentAction || item.status) + ' (' + p + '%)';
                        if (item.rowsCount && item.rowsCount > 0) {
                            text += ' · ' + item.rowsCount.toLocaleString('uk-UA') + ' рядків';
                        }
                        if (item.updatedAt) {
                            text += ' · ' + item.updatedAt;
                        }

                        label.textContent = text;
                    });
                })
                .catch(() => {
                    // тихо ігноруємо
                });
        }

        document.addEventListener('DOMContentLoaded', function () {
            updateStatuses();
            setInterval(updateStatuses, 1000);
        });
    </script>
@endsection
