@extends('layouts.app')

@section('title', 'Прайс · ' . $supplier->name)

@section('content')
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-lg font-semibold tracking-tight">
                Прайс постачальника — {{ $supplier->name }}
            </h1>
            <p class="text-xs text-slate-500">
                Показано всі рядки з файлу
                @if($priceFile)
                    <span class="font-mono text-[11px]">{{ $priceFile->filename }}</span>
                @endif
            </p>
        </div>

        <a href="{{ route('suppliers.edit', $supplier) }}"
           class="px-3 py-1.5 rounded-full bg-slate-200 text-[11px] text-slate-700 hover:bg-slate-300">
            ← Назад до налаштувань
        </a>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
        <div class="p-3 border-b border-slate-100 text-[11px] text-slate-500 flex justify-between">
            <div>
                Рядок заголовків: <span class="font-semibold">{{ $headerRow }}</span>,
                перший рядок даних: <span class="font-semibold">{{ $startRow }}</span>.
            </div>
            @if($priceFile && $priceFile->rows_count)
                <div>
                    Імпортовано рядків: <span class="font-semibold">{{ number_format($priceFile->rows_count, 0, ',', ' ') }}</span>
                </div>
            @endif
        </div>

        <div class="p-3 overflow-auto max-h-[80vh] text-[11px]">
            @if(empty($rows))
                <p class="text-slate-500">
                    Не вдалося прочитати жодного рядка з файлу прайсу.
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
@endsection
