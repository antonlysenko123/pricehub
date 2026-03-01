@extends('layouts.app')

@section('title', 'Редагування постачальника · ' . $supplier->name)

@section('content')
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-lg font-semibold tracking-tight">
                Редагування постачальника — {{ $supplier->name }}
            </h1>
            <p class="text-xs text-slate-500">
                Основні налаштування + мапа колонок. Нижче — декілька рядків з останнього прайсу.
            </p>
        </div>

        <div class="flex items-center gap-2">
            @php $pf = $supplier->latestPriceFile; @endphp

            <div class="hidden sm:block">
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

            <form action="{{ route('suppliers.sync', $supplier) }}" method="POST">
                @csrf
                <button type="submit"
                        class="px-3 py-1.5 rounded-full bg-emerald-600 text-white text-[11px] hover:bg-emerald-700">
                    Оновити прайс
                </button>
            </form>

            <a href="{{ route('suppliers.index') }}"
               class="px-3 py-1.5 rounded-full bg-slate-200 text-[11px] text-slate-700 hover:bg-slate-300">
                ← Назад
            </a>
        </div>
    </div>

    {{-- Тепер просто два блоки один під одним --}}
    <div class="space-y-4">

        {{-- Налаштування + мапа колонок --}}
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
            <form action="{{ route('suppliers.update', $supplier) }}" method="POST" class="p-4 space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-[11px] font-medium text-slate-600 mb-1">
                                Назва
                            </label>
                            <input type="text" name="name" value="{{ old('name', $supplier->name) }}"
                                   class="w-full rounded-md border border-slate-300 text-sm">
                            @error('name')
                            <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-[11px] font-medium text-slate-600 mb-1">
                                Код
                            </label>
                            <input type="text" name="code" value="{{ old('code', $supplier->code) }}"
                                   class="w-full rounded-md border border-slate-300 text-sm">
                            @error('code')
                            <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-[11px] font-medium text-slate-600 mb-1">
                                Тип джерела
                            </label>
                            <select name="type" class="w-full rounded-md border border-slate-300 text-sm">
                                <option value="http"   @selected(old('type', $supplier->type) === 'http')>HTTP / HTTPS</option>
                                <option value="ftp"    @selected(old('type', $supplier->type) === 'ftp')>FTP</option>
                                <option value="gdrive" @selected(old('type', $supplier->type) === 'gdrive')>Google Drive</option>
                            </select>
                            @error('type')
                            <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-[11px] font-medium text-slate-600 mb-1">
                                URL прайсу
                            </label>
                            <input type="text" name="source_url" value="{{ old('source_url', $supplier->source_url) }}"
                                   class="w-full rounded-md border border-slate-300 text-sm font-mono text-[11px]">
                            @error('source_url')
                            <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-[11px] font-medium text-slate-600 mb-1">
                                Формат файлу
                            </label>
                            <select name="ext" class="w-full rounded-md border border-slate-300 text-sm">
                                <option value="">Автовизначення</option>
                                <option value="csv"  @selected(old('ext', $config['ext'] ?? '') === 'csv')>CSV</option>
                                <option value="xls"  @selected(old('ext', $config['ext'] ?? '') === 'xls')>XLS</option>
                                <option value="xlsx" @selected(old('ext', $config['ext'] ?? '') === 'xlsx')>XLSX</option>
                            </select>
                            @error('ext')
                            <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Мапа колонок --}}
                    <div class="space-y-3">
                        @php
                            $headers = $headers ?? [];
                            $headerOptions = [];
                            foreach ($headers as $i => $h) {
                                $label = trim((string)$h) !== '' ? $h : ('Колонка '.($i+1));
                                $headerOptions[$i+1] = $label;
                            }
                        @endphp

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-medium text-slate-600 mb-1">
                                    Рядок заголовків
                                </label>
                                <input type="number" name="header_row"
                                       value="{{ old('header_row', $headerRow) }}"
                                       class="w-full rounded-md border border-slate-300 text-sm">
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-slate-600 mb-1">
                                    Перший рядок даних
                                </label>
                                <input type="number" name="start_row"
                                       value="{{ old('start_row', $startRow) }}"
                                       class="w-full rounded-md border border-slate-300 text-sm">
                            </div>
                        </div>

                        @php
                            function columnSelect($name, $label, $config, $headerOptions) {
                                $current = old($name, $config[$name] ?? null);
                                echo '<div>';
                                echo '<label class="block text-[11px] font-medium text-slate-600 mb-1">'.$label.'</label>';
                                echo '<select name="'.$name.'" class="w-full rounded-md border border-slate-300 text-[11px]">';
                                echo '<option value="">— не використовувати —</option>';
                                foreach ($headerOptions as $index => $title) {
                                    $selected = ($current == $index) ? 'selected' : '';
                                    echo '<option value="'.$index.'" '.$selected.'>'.$index.': '.$title.'</option>';
                                }
                                echo '</select>';
                                echo '</div>';
                            }
                        @endphp

                        <div class="grid grid-cols-2 gap-2">
                            @php columnSelect('col_supplier_sku',      'Артикул постачальника',   $config, $headerOptions); @endphp
                            @php columnSelect('col_manufacturer_sku',  'Артикул виробника',       $config, $headerOptions); @endphp
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            @php columnSelect('col_manufacturer_name', 'Виробник',                $config, $headerOptions); @endphp
                            @php columnSelect('col_barcode',           'Штрихкод',                $config, $headerOptions); @endphp
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            @php columnSelect('col_name',              'Назва товару',            $config, $headerOptions); @endphp
                            @php columnSelect('col_price',             'Ціна закупки',            $config, $headerOptions); @endphp
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            @php columnSelect('col_rrp',               'РРЦ',                     $config, $headerOptions); @endphp
                            @php columnSelect('col_quantity',          'Кількість / наявність',   $config, $headerOptions); @endphp
                        </div>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100 flex justify-end">
                    <button type="submit"
                            class="px-4 py-2 rounded-lg bg-sky-600 text-white text-xs font-medium hover:bg-sky-700">
                        Зберегти налаштування
                    </button>
                </div>
            </form>
        </div>

        {{-- Прев’ю файлу (внизу) --}}
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-slate-800">
                        Зразок рядків з останнього прайсу
                    </h2>
                    <p class="text-[11px] text-slate-500">
                        Показано до 10 рядків, щоб було зручно підбирати колонки.
                    </p>
                </div>
                <a href="{{ route('suppliers.preview', $supplier) }}"
                   class="px-3 py-1.5 rounded-full bg-slate-100 text-[11px] text-slate-700 hover:bg-slate-200">
                    Відкрити весь прайс
                </a>
            </div>

            <div class="p-4 overflow-auto max-h-[420px] text-[11px]">
                @if(!$sampleFile)
                    <p class="text-slate-500">
                        Немає жодного файлу прайсу. Спочатку натисни «Оновити прайс».
                    </p>
                @elseif(empty($rows))
                    <p class="text-slate-500">
                        Не вдалося прочитати жодного рядка. Можливо, прайс ще не завантажували або файл порожній.
                    </p>
                @else
                    <table class="min-w-full border border-slate-200 rounded-lg overflow-hidden">
                        <thead>
                        <tr class="bg-slate-50">
                            @foreach($headers as $i => $h)
                                <th class="px-2 py-1 border-b border-slate-200 text-left font-semibold">
                                    {{ ($i+1) }}:
                                    <span class="font-normal">{{ $h ?: ('Колонка '.($i+1)) }}</span>
                                </th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($rows as $row)
                            <tr class="odd:bg-white even:bg-slate-50/60">
                                @foreach($row as $v)
                                    <td class="px-2 py-1 border-b border-slate-100 whitespace-nowrap">
                                        {{ $v }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        function updateStatuses() {
            fetch('{{ route('suppliers.statuses') }}')
                .then(response => response.json())
                .then(data => {
                    const me = data.find(item => item.id === {{ $supplier->id }});
                    if (!me) return;

                    const bar = document.querySelector('.js-progress-bar');
                    const label = document.querySelector('.js-progress-label');
                    if (!bar || !label) return;

                    if (!me.status) {
                        bar.style.width = '0%';
                        bar.style.backgroundColor = '#cbd5f5';
                        label.textContent = 'Немає прайсу';
                        return;
                    }

                    const p = me.progress || 0;
                    bar.style.width = p + '%';

                    if (me.status === 'imported') {
                        bar.style.backgroundColor = '#22c55e';
                    } else if (me.status === 'failed') {
                        bar.style.backgroundColor = '#ef4444';
                    } else {
                        bar.style.backgroundColor = '#0ea5e9';
                    }

                    let text = (me.currentAction || me.status) + ' (' + p + '%)';
                    if (me.rowsCount && me.rowsCount > 0) {
                        text += ' · ' + me.rowsCount.toLocaleString('uk-UA') + ' рядків';
                    }
                    if (me.updatedAt) {
                        text += ' · ' + me.updatedAt;
                    }

                    label.textContent = text;
                })
                .catch(() => {});
        }

        document.addEventListener('DOMContentLoaded', function () {
            updateStatuses();
            setInterval(updateStatuses, 1000);
        });
    </script>
@endsection
