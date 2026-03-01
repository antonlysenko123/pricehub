<?php

namespace App\Jobs;

use App\Models\Supplier;
use App\Models\PriceFile;
use App\Models\PriceRow;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ImportSupplierPrices implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $supplierId;

    public function __construct(int $supplierId)
    {
        $this->supplierId = $supplierId;
    }

    public function handle(): void
    {
        $supplier = Supplier::find($this->supplierId);
        if (! $supplier) {
            return;
        }

        $config = $supplier->config ?? [];

        /** @var PriceFile|null $priceFile */
        $priceFile = PriceFile::where('supplier_id', $supplier->id)
            ->latest('created_at')
            ->first();

        if (! $priceFile) {
            return;
        }

        if (! Storage::exists($priceFile->filename)) {
            $priceFile->update([
                'status'         => 'failed',
                'current_action' => 'File not found in storage',
                'error_message'  => 'File not found: '.$priceFile->filename,
            ]);
            return;
        }

        $fullPath = Storage::path($priceFile->filename);

        // визначаємо розширення
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        if (! empty($config['ext'])) {
            $ext = strtolower($config['ext']);
        }

        $priceFile->update([
            'status'         => 'importing',
            'progress'       => 0,
            'current_action' => 'Importing',
            'rows_count'     => 0,
            'error_message'  => null,
        ]);

        $skipped = 0;

        try {
            if ($ext === 'csv') {
                [$rowsTotal, $skipped] = $this->importCsv($priceFile, $fullPath, $config);
            } else {
                [$rowsTotal, $skipped] = $this->importExcel($priceFile, $fullPath, $config);
            }

            $msg = 'Imported';
            if ($skipped > 0) {
                $msg .= " (skipped {$skipped} rows)";
            }

            $priceFile->update([
                'status'         => 'imported',
                'progress'       => 100,
                'current_action' => $msg,
                'rows_count'     => $rowsTotal,
                'error_message'  => $skipped > 0 ? "Some rows were skipped during import ({$skipped})." : null,
            ]);
        } catch (\Throwable $e) {
            $priceFile->update([
                'status'         => 'failed',
                'current_action' => 'Import failed',
                'error_message'  => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /* ---------- CSV ---------- */

    /**
     * @return array [importedCount, skippedCount]
     */
    protected function importCsv(PriceFile $priceFile, string $fullPath, array $config): array
    {
        $this->clearOldRows($priceFile);

        $headerRow = (int) ($config['header_row'] ?? 1);
        $startRow  = (int) ($config['start_row']  ?? ($headerRow + 1));

        // перший прохід – визначаємо роздільник і рахуємо рядки
        $handle = fopen($fullPath, 'r');
        if (! $handle) {
            throw new \RuntimeException("Cannot open CSV: {$fullPath}");
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            return [0, 0];
        }

        $delimiter      = ';';
        $countSemicolon = substr_count($firstLine, ';');
        $countComma     = substr_count($firstLine, ',');

        if ($countComma > $countSemicolon) {
            $delimiter = ',';
        }

        $total = 1; // ми вже прочитали перший
        while (fgets($handle) !== false) {
            $total++;
        }
        fclose($handle);

        // другий прохід – реально парсимо
        $handle = fopen($fullPath, 'r');
        if (! $handle) {
            throw new \RuntimeException("Cannot reopen CSV: {$fullPath}");
        }

        $idx = fn (?int $n) => $n ? $n - 1 : null;

        $colSupplierSku      = $idx($config['col_supplier_sku']      ?? null);
        $colManufacturerSku  = $idx($config['col_manufacturer_sku']  ?? null);
        $colManufacturerName = $idx($config['col_manufacturer_name'] ?? null);
        $colBarcode          = $idx($config['col_barcode']           ?? null);
        $colName             = $idx($config['col_name']              ?? null);
        $colPrice            = $idx($config['col_price']             ?? null);
        $colRrp              = $idx($config['col_rrp']               ?? null);
        $colQuantity         = $idx($config['col_quantity']          ?? null);

        $currentRowNumber = 0;
        $imported         = 0;
        $skipped          = 0;
        $lastPercent      = 0;

        while (($rowArr = fgetcsv($handle, 0, $delimiter)) !== false) {
            $currentRowNumber++;

            if ($currentRowNumber < $startRow) {
                continue;
            }

            // чистимо крякозябри
            $rowArr = $this->cleanRow($rowArr);

            $get = function (?int $col) use ($rowArr) {
                return $col !== null && array_key_exists($col, $rowArr)
                    ? trim((string) $rowArr[$col])
                    : null;
            };

            $supplierSku      = $this->limitLength($get($colSupplierSku), 255);
            $manufacturerSku  = $this->limitLength($get($colManufacturerSku), 255);
            $manufacturerName = $this->limitLength($get($colManufacturerName), 255);
            $barcode          = $this->limitLength($get($colBarcode), 255);
            $name             = $get($colName);
            $priceRaw         = $get($colPrice);
            $rrpRaw           = $get($colRrp);
            $qtyRaw           = $get($colQuantity);

            if (! $name && ! $supplierSku && ! $manufacturerSku) {
                continue;
            }

            if ($name === null || $name === '') {
                $name = $supplierSku ?: $manufacturerSku ?: 'Без назви';
            }
            $name = $this->limitLength($name, 255);

            $price = $this->toFloat($priceRaw);
            $rrp   = $this->toFloat($rrpRaw);
            $qty   = $this->toInt($qtyRaw);

            try {
                PriceRow::create([
                    'price_file_id'       => $priceFile->id,
                    'supplier_sku'        => $supplierSku,
                    'manufacturer_sku'    => $manufacturerSku,
                    'manufacturer_name'   => $manufacturerName,
                    'barcode'             => $barcode,
                    'name'                => $name,
                    'category'            => null,
                    'price'               => $price,
                    'rrp'                 => $rrp,
                    'quantity'            => $qty,
                    'availability_status' => null,
                    'raw'                 => $rowArr,
                ]);

                $imported++;
            } catch (\Throwable $e) {
                // Якщо якась строка не влазить у БД — пропускаємо її
                $skipped++;
            }

            if ($total > 0) {
                $percent = (int) floor($currentRowNumber / $total * 100);
                if ($percent >= $lastPercent + 1 || $currentRowNumber === $total) {
                    $priceFile->update([
                        'progress'       => $percent,
                        'rows_count'     => $imported,
                        'current_action' => "Importing ({$imported} / {$total})",
                    ]);
                    $lastPercent = $percent;
                }
            }
        }

        fclose($handle);

        return [$imported, $skipped];
    }

    /* ---------- Excel ---------- */

    /**
     * @return array [importedCount, skippedCount]
     */
    protected function importExcel(PriceFile $priceFile, string $fullPath, array $config): array
    {
        $this->clearOldRows($priceFile);

        $headerRow = (int) ($config['header_row'] ?? 1);
        $startRow  = (int) ($config['start_row']  ?? ($headerRow + 1));

        $collection = Excel::toCollection(null, $fullPath)->first();
        if (! $collection) {
            return [0, 0];
        }

        $rows  = $collection->toArray();
        $total = count($rows);

        $idx = fn (?int $n) => $n ? $n - 1 : null;

        $colSupplierSku      = $idx($config['col_supplier_sku']      ?? null);
        $colManufacturerSku  = $idx($config['col_manufacturer_sku']  ?? null);
        $colManufacturerName = $idx($config['col_manufacturer_name'] ?? null);
        $colBarcode          = $idx($config['col_barcode']           ?? null);
        $colName             = $idx($config['col_name']              ?? null);
        $colPrice            = $idx($config['col_price']             ?? null);
        $colRrp              = $idx($config['col_rrp']               ?? null);
        $colQuantity         = $idx($config['col_quantity']          ?? null);

        $currentRowNumber = 0;
        $imported         = 0;
        $skipped          = 0;
        $lastPercent      = 0;

        foreach ($rows as $rowArr) {
            $currentRowNumber++;

            if ($currentRowNumber < $startRow) {
                continue;
            }

            $rowArr = $this->cleanRow($rowArr);

            $get = function (?int $col) use ($rowArr) {
                return $col !== null && array_key_exists($col, $rowArr)
                    ? trim((string) $rowArr[$col])
                    : null;
            };

            $supplierSku      = $this->limitLength($get($colSupplierSku), 255);
            $manufacturerSku  = $this->limitLength($get($colManufacturerSku), 255);
            $manufacturerName = $this->limitLength($get($colManufacturerName), 255);
            $barcode          = $this->limitLength($get($colBarcode), 255);
            $name             = $get($colName);
            $priceRaw         = $get($colPrice);
            $rrpRaw           = $get($colRrp);
            $qtyRaw           = $get($colQuantity);

            if (! $name && ! $supplierSku && ! $manufacturerSku) {
                continue;
            }

            if ($name === null || $name === '') {
                $name = $supplierSku ?: $manufacturerSku ?: 'Без назви';
            }
            $name = $this->limitLength($name, 255);

            $price = $this->toFloat($priceRaw);
            $rrp   = $this->toFloat($rrpRaw);
            $qty   = $this->toInt($qtyRaw);

            try {
                PriceRow::create([
                    'price_file_id'       => $priceFile->id,
                    'supplier_sku'        => $supplierSku,
                    'manufacturer_sku'    => $manufacturerSku,
                    'manufacturer_name'   => $manufacturerName,
                    'barcode'             => $barcode,
                    'name'                => $name,
                    'category'            => null,
                    'price'               => $price,
                    'rrp'                 => $rrp,
                    'quantity'            => $qty,
                    'availability_status' => null,
                    'raw'                 => $rowArr,
                ]);

                $imported++;
            } catch (\Throwable $e) {
                $skipped++;
            }

            if ($total > 0) { 
                $percent = (int) floor($currentRowNumber / $total * 100);
                if ($percent >= $lastPercent + 1 || $currentRowNumber === $total) {
                    $priceFile->update([
                        'progress'       => $percent,
                        'rows_count'     => $imported,
                        'current_action' => "Importing ({$imported} / {$total})",
                    ]);
                    $lastPercent = $percent;
                }
            }
        }

        return [$imported, $skipped];
    }

    /* ---------- допоміжні ---------- */

    protected function cleanString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        $converted = @mb_convert_encoding($value, 'UTF-8', 'UTF-8, Windows-1251, CP1251, ISO-8859-1');

        if (! mb_check_encoding($converted, 'UTF-8')) {
            $converted = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        }

        return $converted;
    }

    protected function cleanRow(array $row): array
    {
        foreach ($row as $k => $v) {
            if (is_string($v)) {
                $row[$k] = $this->cleanString($v);
            }
        }
        return $row;
    }

    protected function limitLength(?string $value, int $max): ?string
    {
        if ($value === null) {
            return null;
        }
        if (mb_strlen($value, 'UTF-8') <= $max) {
            return $value;
        }
        return mb_substr($value, 0, $max, 'UTF-8');
    }

    protected function clearOldRows(PriceFile $priceFile): void
    {
        PriceRow::where('price_file_id', $priceFile->id)->delete();
    }

    protected function toFloat(?string $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        $value = str_replace([' ', ','], ['', '.'], $value);
        return is_numeric($value) ? (float) $value : null;
    }

    protected function toInt(?string $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        $value = preg_replace('/[^\d\-]/', '', $value);
        return $value !== '' ? (int) $value : null;
    }
}
