@extends('layouts.app')

@section('title', 'Edit supplier Ј Price Hub')

@section('content')
    <div class="flex items-center justify-between mb-3">
        <div>
            <h1 class="text-lg font-semibold tracking-tight">Edit supplier</h1>
            <p class="text-xs text-slate-500">–едагуванн€ джерела: {{ $supplier->name }}</p>
        </div>
        <a href="{{ route('suppliers.index') }}"
           class="text-xs text-slate-500 hover:text-slate-700">
            < Back to suppliers
        </a>
    </div>

    <form action="{{ route('suppliers.update', $supplier) }}" method="POST"
          class="bg-white border border-slate-200 rounded-xl shadow-sm p-4 space-y-4 max-w-3xl">
        @csrf
        @method('PUT')

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium mb-1">Name</label>
                <input type="text" name="name" value="{{ old('name', $supplier->name) }}"
                       class="w-full border rounded-lg px-3 py-2 text-xs"
                       required>
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Code</label>
                <input type="text" name="code" value="{{ old('code', $supplier->code) }}"
                       class="w-full border rounded-lg px-3 py-2 text-xs font-mono"
                       required>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-medium mb-1">Source type</label>
                <select name="type"
                        class="w-full border rounded-lg px-3 py-2 text-xs">
                    <option value="http"  @selected(old('type', $supplier->type) === 'http')>HTTP</option>
                    <option value="ftp"   @selected(old('type', $supplier->type) === 'ftp')>FTP</option>
                    <option value="gdrive"@selected(old('type', $supplier->type) === 'gdrive')>Google Drive</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-medium mb-1">Price URL</label>
                <input type="text" name="source_url" value="{{ old('source_url', $supplier->source_url) }}"
                       class="w-full border rounded-lg px-3 py-2 text-xs font-mono">
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-medium mb-1">File extension</label>
                <select name="ext" class="w-full border rounded-lg px-3 py-2 text-xs">
                    @php $ext = old('ext', $config['ext'] ?? ''); @endphp
                    <option value="">Auto</option>
                    <option value="csv"  @selected($ext === 'csv')>CSV</option>
                    <option value="xls"  @selected($ext === 'xls')>XLS</option>
                    <option value="xlsx" @selected($ext === 'xlsx')>XLSX</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Header row</label>
                <input type="number" name="header_row"
                       value="{{ old('header_row', $config['header_row'] ?? 1) }}"
                       class="w-full border rounded-lg px-3 py-2 text-xs">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">First data row</label>
                <input type="number" name="start_row"
                       value="{{ old('start_row', $config['start_row'] ?? 2) }}"
                       class="w-full border rounded-lg px-3 py-2 text-xs">
            </div>
        </div>

        <div class="border-t pt-3 mt-1">
            <h2 class="text-xs font-semibold text-slate-600 mb-2">
                Column mapping (можна тонко налаштувати через УMap columnsФ)
            </h2>

            <div class="grid md:grid-cols-4 gap-3 text-xs">
                @php
                    $cfg = $config ?? [];
                    $fields = [
                        'col_supplier_sku'      => 'Supplier SKU',
                        'col_manufacturer_sku'  => 'Manufacturer SKU',
                        'col_manufacturer_name' => 'Manufacturer name',
                        'col_barcode'           => 'Barcode',
                        'col_name'              => 'Name',
                        'col_price'             => 'Price',
                        'col_rrp'               => 'RRP',
                        'col_quantity'          => 'Quantity',
                    ];
                @endphp

                @foreach($fields as $name => $label)
                    <div>
                        <label class="block text-[11px] font-medium mb-1">{{ $label }} (column #)</label>
                        <input type="number" name="{{ $name }}"
                               value="{{ old($name, $cfg[$name] ?? null) }}"
                               class="w-full border rounded-lg px-2 py-1 text-[11px]">
                    </div>
                @endforeach
            </div>
        </div>

        <div class="pt-2 flex gap-2">
            <button type="submit"
                    class="px-4 py-2 bg-sky-600 text-white text-xs rounded-lg hover:bg-sky-700">
                Save changes
            </button>
            <a href="{{ route('suppliers.index') }}"
               class="px-4 py-2 bg-slate-100 text-xs rounded-lg hover:bg-slate-200">
                Cancel
            </a>
        </div>
    </form>
@endsection
