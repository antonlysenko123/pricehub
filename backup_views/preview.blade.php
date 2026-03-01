@extends('layouts.app')

@section('title', 'Map columns · Price Hub')

@section('content')
    <div class="flex items-center justify-between mb-3">
        <div>
            <h1 class="text-lg font-semibold tracking-tight">Map columns</h1>
            <p class="text-xs text-slate-500">
                {{ $supplier->name }} · файл: <span class="font-mono">{{ $priceFile->filename }}</span>
            </p>
        </div>
        <a href="{{ route('suppliers.index') }}"
           class="text-xs text-slate-500 hover:text-slate-700">
            < Back to suppliers
        </a>
    </div>

    <div class="mb-4 space-y-2 text-[11px] text-slate-500">
        <div>Header row: {{ $headerRow }}, first data row: {{ $startRow }}</div>
        <div>Спочатку перевір структуру, потім обери, яка колонка за що відповідає.</div>
    </div>

    {{-- Preview --}}
    <div class="mb-4 bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-2 border-b bg-slate-50 text-xs font-semibold text-slate-600">
            Preview (headers + first rows)
        </div>
        <div class="max-h-[50vh] overflow-auto scrollbar-thin">
            <table class="min-w-full text-[11px]">
                <thead class="bg-slate-50 border-b border-slate-200 sticky top-0 z-10">
                <tr>
                    <th class="px-2 py-1 text-left text-slate-500">#</th>
                    @foreach($headers as $i => $label)
                        <th class="px-2 py-1 text-left text-slate-600 border-l border-slate-200">
                            {{ $i + 1 }}<br>
                            <span class="font-normal text-[10px]">{{ $label }}</span>
                        </th>
                    @endforeach
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse($rows as $rowIndex => $row)
                    <tr>
                        <td class="px-2 py-1 text-slate-400">{{ $startRow + $rowIndex }}</td>
                        @foreach($headers as $i => $label)
                            <td class="px-2 py-1 border-l border-slate-50">
                                {{ $row[$i] ?? '' }}
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($headers) + 1 }}" class="px-2 py-3 text-center text-slate-500">
                            Даних не знайдено. Перевір header row / first data row.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mapping form --}}
    <form action="{{ route('suppliers.mapping', $supplier) }}" method="POST"
          class="bg-white border border-slate-200 rounded-xl shadow-sm p-4 space-y-4 max-w-4xl">
        @csrf

        <div class="grid md:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-medium mb-1">Header row</label>
                <input type="number" name="header_row"
                       value="{{ old('header_row', $headerRow) }}"
                       class="w-full border rounded-lg px-3 py-2 text-xs">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">First data row</label>
                <input type="number" name="start_row"
                       value="{{ old('start_row', $startRow) }}"
                       class="w-full border rounded-lg px-3 py-2 text-xs">
            </div>
        </div>

        @php
            $options = [];
            foreach ($headers as $i => $label) {
                $options[$i + 1] = ($i + 1) . ': ' . $label;
            }

            $cfg = $config ?? [];
            $fieldMap = [
                'col_supplier_sku'      => 'Supplier SKU',
                'col_manufacturer_sku'  => 'Manufacturer SKU (model)',
                'col_manufacturer_name' => 'Manufacturer name',
                'col_barcode'           => 'Barcode / EAN',
                'col_name'              => 'Product name',
                'col_price'             => 'Price',
                'col_rrp'               => 'RRP',
                'col_quantity'          => 'Quantity / stock',
            ];
        @endphp

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            @foreach($fieldMap as $field => $label)
                <div>
                    <label class="block text-[11px] font-medium mb-1">{{ $label }}</label>
                    <select name="{{ $field }}"
                            class="w-full border rounded-lg px-2 py-1 text-[11px]">
                        <option value="">— Not used —</option>
                        @foreach($options as $value => $text)
                            <option value="{{ $value }}"
                                @if( (old($field, $cfg[$field] ?? '') == $value)) selected @endif>
                                {{ $text }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endforeach
        </div>

        <div class="flex gap-2 pt-1">
            <button type="submit"
                    class="px-4 py-2 bg-emerald-600 text-white text-xs rounded-lg hover:bg-emerald-700">
                Save mapping
            </button>
            <a href="{{ route('suppliers.index') }}"
               class="px-4 py-2 bg-slate-100 text-xs rounded-lg hover:bg-slate-200">
                Cancel
            </a>
        </div>

        <p class="mt-2 text-[11px] text-slate-500">
            Після збереження мапінгу повернись на список постачальників та натисни
            <span class="font-semibold">Import</span>, щоб перезалити прайс з новими колонками.
        </p>
    </form>
@endsection
