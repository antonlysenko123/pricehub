<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\PriceFile;
use App\Models\PriceRow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Jobs\ImportSupplierPrices;
use App\Jobs\SyncSupplierPrices;
use App\Jobs\FetchSupplierPrice;

class SupplierController extends Controller
{
    /**
     * Список постачальників з останнім файлом прайсу.
     */
    public function index()
    {
        $suppliers = Supplier::with('latestPriceFile')
            ->orderBy('name')
            ->get();

        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Форма створення постачальника.
     */
    public function create()
    {
        return view('suppliers.create');
    }

    /**
     * Збереження нового постачальника.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'code'       => 'required|string|max:50|unique:suppliers,code',
            'type'       => 'required|string|in:http,ftp,gdrive',
            'source_url' => 'nullable|string|max:1000',
            'ext'        => 'nullable|string|in:xls,xlsx,csv',

            'header_row' => 'nullable|integer|min:1',
            'start_row'  => 'nullable|integer|min:1',

            'col_supplier_sku'        => 'nullable|integer|min:1',
            'col_manufacturer_sku'    => 'nullable|integer|min:1',
            'col_manufacturer_name'   => 'nullable|integer|min:1',
            'col_barcode'             => 'nullable|integer|min:1',
            'col_name'                => 'nullable|integer|min:1',
            'col_price'               => 'nullable|integer|min:1',
            'col_rrp'                 => 'nullable|integer|min:1',
            'col_quantity'            => 'nullable|integer|min:1',
        ]);

        $config = [
            'ext'        => $data['ext']        ?? 'xlsx',
            'header_row' => $data['header_row'] ?? 1,
            'start_row'  => $data['start_row']  ?? 2,

            'col_supplier_sku'      => $data['col_supplier_sku']      ?? null,
            'col_manufacturer_sku'  => $data['col_manufacturer_sku']  ?? null,
            'col_manufacturer_name' => $data['col_manufacturer_name'] ?? null,
            'col_barcode'           => $data['col_barcode']           ?? null,
            'col_name'              => $data['col_name']              ?? null,
            'col_price'             => $data['col_price']             ?? null,
            'col_rrp'               => $data['col_rrp']               ?? null,
            'col_quantity'          => $data['col_quantity']          ?? null,
        ];

        Supplier::create([
            'name'       => $data['name'],
            'code'       => $data['code'],
            'type'       => $data['type'],
            'source_url' => $data['source_url'] ?? null,
            'config'     => $config,
        ]);

        return redirect()->route('suppliers.index')
            ->with('status', 'Постачальника створено.');
    }

    /**
     * Форма редагування постачальника з мапою колонок + превʼю.
     */
    public function edit(Supplier $supplier)
    {
        // щоб був latestPriceFile
        $supplier->load('latestPriceFile');

        $config = $supplier->config ?? [];

        // тягнемо зразок для превʼю (до 10 рядків)
        [$sampleFile, $headers, $rows, $headerRow, $startRow] = $this->getSampleForSupplier($supplier);

        return view('suppliers.edit', [
            'supplier'   => $supplier,
            'config'     => $config,
            'sampleFile' => $sampleFile,
            'headers'    => $headers,
            'rows'       => $rows,
            'headerRow'  => $headerRow,
            'startRow'   => $startRow,
        ]);
    }

    /**
     * Оновлення постачальника + збереження мапи колонок.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'code'       => 'required|string|max:50|unique:suppliers,code,' . $supplier->id,
            'type'       => 'required|string|in:http,ftp,gdrive',
            'source_url' => 'nullable|string|max:1000',
            'ext'        => 'nullable|string|in:xls,xlsx,csv',

            'header_row' => 'nullable|integer|min:1',
            'start_row'  => 'nullable|integer|min:1',

            'col_supplier_sku'        => 'nullable|integer|min:1',
            'col_manufacturer_sku'    => 'nullable|integer|min:1',
            'col_manufacturer_name'   => 'nullable|integer|min:1',
            'col_barcode'             => 'nullable|integer|min:1',
            'col_name'                => 'nullable|integer|min:1',
            'col_price'               => 'nullable|integer|min:1',
            'col_rrp'                 => 'nullable|integer|min:1',
            'col_quantity'            => 'nullable|integer|min:1',
        ]);

        $config = $supplier->config ?? [];

        $config['ext']        = $data['ext']        ?? ($config['ext']        ?? 'xlsx');
        $config['header_row'] = $data['header_row'] ?? ($config['header_row'] ?? 1);
        $config['start_row']  = $data['start_row']  ?? ($config['start_row']  ?? 2);

        $config['col_supplier_sku']      = $data['col_supplier_sku']      ?? ($config['col_supplier_sku']      ?? null);
        $config['col_manufacturer_sku']  = $data['col_manufacturer_sku']  ?? ($config['col_manufacturer_sku']  ?? null);
        $config['col_manufacturer_name'] = $data['col_manufacturer_name'] ?? ($config['col_manufacturer_name'] ?? null);
        $config['col_barcode']           = $data['col_barcode']           ?? ($config['col_barcode']           ?? null);
        $config['col_name']              = $data['col_name']              ?? ($config['col_name']              ?? null);
        $config['col_price']             = $data['col_price']             ?? ($config['col_price']             ?? null);
        $config['col_rrp']               = $data['col_rrp']               ?? ($config['col_rrp']               ?? null);
        $config['col_quantity']          = $data['col_quantity']          ?? ($config['col_quantity']          ?? null);

        $supplier->update([
            'name'       => $data['name'],
            'code'       => $data['code'],
            'type'       => $data['type'],
            'source_url' => $data['source_url'] ?? null,
            'config'     => $config,
        ]);

        return redirect()->route('suppliers.edit', $supplier)
            ->with('status', 'Налаштування постачальника збережено.');
    }

    /**
     * Видалення постачальника разом з усіма його прайсами.
     */
    public function destroy(Supplier $supplier)
    {
        $priceFileIds = PriceFile::where('supplier_id', $supplier->id)->pluck('id');

        if ($priceFileIds->isNotEmpty()) {
            PriceRow::whereIn('price_file_id', $priceFileIds)->delete();
            PriceFile::whereIn('id', $priceFileIds)->delete();
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('status', 'Постачальника та всі його прайси видалено.');
    }

    /**
     * Лише завантаження прайсу (черга).
     */
    public function fetch(Supplier $supplier)
    {
        FetchSupplierPrice::dispatch($supplier->id);

        return redirect()->route('suppliers.index')
            ->with('status', "Завантаження прайсу для {$supplier->name} поставлено в чергу.");
    }

    /**
     * Лише імпорт останнього прайсу в rows (черга).
     */
    public function import(Supplier $supplier)
    {
        ImportSupplierPrices::dispatch($supplier->id);

        return redirect()->route('suppliers.index')
            ->with('status', "Імпорт для {$supplier->name} поставлено в чергу.");
    }

    /**
     * Повний цикл: завантажити + імпортувати (черга).
     */
    public function sync(Supplier $supplier)
    {
        SyncSupplierPrices::dispatch($supplier->id);

        return back()->with('status', "Оновлення прайсу для {$supplier->name} поставлено в чергу.");
    }

    /**
     * JSON-статуси для прогрес-барів у списку постачальників.
     */
    public function statuses()
    {
        $suppliers = Supplier::with('latestPriceFile')
            ->orderBy('id')
            ->get();

        $data = $suppliers->map(function (Supplier $supplier) {
            $pf = $supplier->latestPriceFile;

            return [
                'id'            => $supplier->id,
                'status'        => $pf?->status,
                'progress'      => $pf?->progress ?? 0,
                'currentAction' => $pf?->current_action,
                'rowsCount'     => $pf?->rows_count ?? 0,
                'updatedAt'     => $pf?->updated_at
                    ? $pf->updated_at->format('d.m.Y H:i')
                    : null,
            ];
        });

        return response()->json($data);
    }

    /**
     * Окремий превʼю-екран — ВЕСЬ прайс (усі рядки).
     */
    public function preview(Supplier $supplier)
    {
        $config = $supplier->config ?? [];

        $headerRow = (int)($config['header_row'] ?? 1);
        $startRow  = (int)($config['start_row']  ?? ($headerRow + 1));

        $priceFile = PriceFile::where('supplier_id', $supplier->id)
            ->orderByDesc('created_at')
            ->first();

        if (!$priceFile) {
            return redirect()->route('suppliers.edit', $supplier)
                ->with('status', "Немає жодного файлу прайсу для {$supplier->name}. Спочатку онови прайс.");
        }

        // шукаємо файл з урахуванням private/
        $path = $priceFile->filename;
        if (!Storage::exists($path)) {
            if (!str_starts_with($path, 'private/')) {
                $alt = 'private/' . ltrim($path, '/');
                if (Storage::exists($alt)) {
                    $path = $alt;
                    $priceFile->filename = $alt;
                } else {
                    return redirect()->route('suppliers.edit', $supplier)
                        ->with('status', "Файл прайсу не знайдено в storage.");
                }
            } else {
                return redirect()->route('suppliers.edit', $supplier)
                    ->with('status', "Файл прайсу не знайдено в storage.");
            }
        }

        $fullPath = Storage::path($path);

        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        if (!empty($config['ext'])) {
            $ext = strtolower($config['ext']);
        }

        $headers = [];
        $rows    = [];

        if ($ext === 'csv') {
            $handle = fopen($fullPath, 'r');
            if ($handle) {
                $firstLine = fgets($handle);
                if ($firstLine !== false) {
                    $delimiter      = ';';
                    $countSemicolon = substr_count($firstLine, ';');
                    $countComma     = substr_count($firstLine, ',');

                    if ($countComma > $countSemicolon) {
                        $delimiter = ',';
                    }

                    rewind($handle);

                    $current = 0;
                    while (($rowArr = fgetcsv($handle, 0, $delimiter)) !== false) {
                        $current++;

                        // чистимо крякозябри
                        $rowArr = $this->cleanRow($rowArr);

                        if ($current == $headerRow) {
                            $headers = $rowArr;
                        }

                        if ($current >= $startRow) {
                            $rows[] = $rowArr;
                        }
                    }
                }

                fclose($handle);
            }
        } else {
            // Excel повністю
            $sheet = Excel::toCollection(null, $fullPath)->first();
            if ($sheet) {
                $current = 0;
                foreach ($sheet as $row) {
                    $current++;
                    $rowArr = $row->toArray();

                    // чистимо крякозябри
                    $rowArr = $this->cleanRow($rowArr);

                    if ($current == $headerRow) {
                        $headers = $rowArr;
                    }

                    if ($current >= $startRow) {
                        $rows[] = $rowArr;
                    }
                }
            }
        }

        // fallback заголовків, якщо заголовковий рядок пустий
        if (empty($headers) && !empty($rows)) {
            $headers = array_map(function ($i) {
                return 'Колонка ' . ($i + 1);
            }, array_keys($rows[0]));
        }

        return view('suppliers.preview', [
            'supplier'  => $supplier,
            'priceFile' => $priceFile,
            'rows'      => $rows,
            'headers'   => $headers,
            'headerRow' => $headerRow,
            'startRow'  => $startRow,
        ]);
    }

    /**
     * Хелпер: вибірка до 10 рядків для превʼю в edit().
     *
     * @return array [PriceFile|null, array $headers, array $rows, int $headerRow, int $startRow]
     */
    protected function getSampleForSupplier(Supplier $supplier): array
    {
        $config = $supplier->config ?? [];

        $headerRow = (int)($config['header_row'] ?? 1);
        $startRow  = (int)($config['start_row']  ?? ($headerRow + 1));

        $priceFile = PriceFile::where('supplier_id', $supplier->id)
            ->orderByDesc('created_at')
            ->first();

        $headers     = [];
        $rows        = [];
        $headerCells = [];

        if (!$priceFile) {
            return [null, $headers, $rows, $headerRow, $startRow];
        }

        // шукаємо реальний файл (з урахуванням private/)
        $path = $priceFile->filename;

        if (!Storage::exists($path)) {
            if (!str_starts_with($path, 'private/')) {
                $alt = 'private/' . ltrim($path, '/');
                if (Storage::exists($alt)) {
                    $path = $alt;
                    $priceFile->filename = $alt;
                } else {
                    return [$priceFile, $headers, $rows, $headerRow, $startRow];
                }
            } else {
                return [$priceFile, $headers, $rows, $headerRow, $startRow];
            }
        }

        $fullPath = Storage::path($path);

        // намагаємось вгадати формат
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        if (!empty($config['ext'])) {
            $ext = strtolower($config['ext']);
        }

        $maxRows = 10;

        if ($ext === 'csv') {
            // ===== CSV режим =====
            $handle = @fopen($fullPath, 'r');
            if ($handle) {
                $firstLine = fgets($handle);
                if ($firstLine !== false) {
                    $delimiter      = ';';
                    $countSemicolon = substr_count($firstLine, ';');
                    $countComma     = substr_count($firstLine, ',');

                    if ($countComma > $countSemicolon) {
                        $delimiter = ',';
                    }

                    rewind($handle);

                    $current = 0;
                    while (($rowArr = fgetcsv($handle, 0, $delimiter)) !== false) {
                        $current++;

                        // чистимо рядок
                        $rowArr = $this->cleanRow($rowArr);

                        if ($current == $headerRow) {
                            $headerCells = $rowArr;
                        }

                        if ($current >= $startRow && count($rows) < $maxRows) {
                            $rows[] = $rowArr;
                        }

                        if ($current > $startRow + $maxRows + 5) {
                            break;
                        }
                    }
                }

                fclose($handle);
            }
        } else {
            // ===== Excel режим (xls/xlsx/ods) з захистом try/catch =====
            try {
                $sheet = Excel::toCollection(null, $fullPath)->first();

                if ($sheet) {
                    $current = 0;
                    foreach ($sheet as $row) {
                        $current++;
                        $rowArr = $row->toArray();

                        // чистимо рядок
                        $rowArr = $this->cleanRow($rowArr);

                        if ($current == $headerRow) {
                            $headerCells = $rowArr;
                        }

                        if ($current >= $startRow && count($rows) < $maxRows) {
                            $rows[] = $rowArr;
                        }

                        if ($current > $startRow + $maxRows + 5) {
                            break;
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Якщо не вийшло прочитати як Excel — не роняємо сторінку
                return [$priceFile, $headers, $rows, $headerRow, $startRow];
            }
        }

        if (!empty($headerCells)) {
            $headers = $headerCells;
        } elseif (!empty($rows)) {
            $headers = array_map(function ($i) {
                return 'Колонка ' . ($i + 1);
            }, array_keys($rows[0]));
        }

        return [$priceFile, $headers, $rows, $headerRow, $startRow];
    }

    /**
     * Нормалізує один рядок тексту до UTF-8.
     */
    protected function cleanString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        // Якщо вже валідний UTF-8 — нічого не робимо
        if (mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        // Пробуємо перекодувати з найтиповіших «віндових» кодувань
        $converted = @mb_convert_encoding($value, 'UTF-8', 'UTF-8, Windows-1251, CP1251, ISO-8859-1');

        // Якщо все ще не ок — викидаємо проблемні байти
        if (! mb_check_encoding($converted, 'UTF-8')) {
            $converted = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        }

        return $converted;
    }

    /**
     * Пройтись по всьому рядку (масиву колонок) і почистити строки.
     */
    protected function cleanRow(array $row): array
    {
        foreach ($row as $k => $v) {
            if (is_string($v)) {
                $row[$k] = $this->cleanString($v);
            }
        }
        return $row;
    }
}
