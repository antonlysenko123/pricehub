<?php

namespace App\Console\Commands;

use App\Models\PriceFile;
use App\Models\PriceRow;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ImportPrices extends Command
{
    protected $signature = 'prices:import {--supplier=} {--file=}';
    protected $description = 'Parse downloaded price files into price_rows table';

    public function handle(): int
    {
        $query = PriceFile::with('supplier')
            ->whereIn('status', ['downloaded', 'parsed']); // дозволяємо перев імпорт

        if ($supplierId = $this->option('supplier')) {
            $query->where('supplier_id', $supplierId);
        }

        if ($fileId = $this->option('file')) {
            $query->where('id', $fileId);
        }

        $files = $query->get();

        if ($files->isEmpty()) {
            $this->warn('No price files to import.');
            return 0;
        }

        foreach ($files as $file) {
            $this->info("Importing file #{$file->id} ({$file->filename}) for supplier {$file->supplier->code}");

            try {
                $this->importFile($file);
                $file->status = 'parsed';
                $file->save();
                $this->info("  Done.");
            } catch (\Throwable $e) {
                $this->error("  Error: " . $e->getMessage());
                $file->status = 'failed';
                $file->error_message = $e->getMessage();
                $file->save();
            }
        }

        return 0;
    }

    protected function importFile(PriceFile $file): void
    {
        $supplier = $file->supplier;
        $config = $supplier->config ?? [];

        $path = $file->filename;
// якщо файлу нема – пробуємо з префіксом "private/"
if (!Storage::exists($path) && !str_starts_with($path, 'private/')) {
    $alt = 'private/' . ltrim($path, '/');
    if (Storage::exists($alt)) {
        $path = $alt;
    }
}

        if (!Storage::exists($path)) {
            throw new \RuntimeException("File not found in storage: {$path}");
        }

        $fullPath = Storage::path($path);
        $this->line("  Reading: {$fullPath}");

        // first sheet
        $collection = Excel::toCollection(null, $fullPath)->first();

        if (!$collection) {
            throw new \RuntimeException("Empty spreadsheet.");
        }

        $headerRow = (int)($config['header_row'] ?? 1);
        $startRow  = (int)($config['start_row']  ?? ($headerRow + 1));

        // 1-based -> 0-based
        $idx = function (?int $n): ?int {
            return $n ? $n - 1 : null;
        };

        $colSupplierSku      = $idx($config['col_supplier_sku']      ?? null);
        $colManufacturerSku  = $idx($config['col_manufacturer_sku']  ?? null);
        $colManufacturerName = $idx($config['col_manufacturer_name'] ?? null);
        $colBarcode          = $idx($config['col_barcode']           ?? null);
        $colName             = $idx($config['col_name']              ?? null);
        $colPrice            = $idx($config['col_price']             ?? null);
        $colRrp              = $idx($config['col_rrp']               ?? null);
        $colQuantity         = $idx($config['col_quantity']          ?? null);

        $rowsCount = 0;

        // remove previous rows for this file
        PriceRow::where('price_file_id', $file->id)->delete();

        foreach ($collection as $rowIndex => $row) {
            $excelRowNumber = $rowIndex + 1;

            if ($excelRowNumber < $startRow) {
                continue;
            }

            $rowArr = $row->toArray();

            $get = function (?int $col) use ($rowArr) {
                return $col !== null && array_key_exists($col, $rowArr)
                    ? trim((string)$rowArr[$col])
                    : null;
            };

            $supplierSku      = $get($colSupplierSku);
            $manufacturerSku  = $get($colManufacturerSku);
            $manufacturerName = $get($colManufacturerName);
            $barcode          = $get($colBarcode);
            $name             = $get($colName);
            $priceRaw         = $get($colPrice);
            $rrpRaw           = $get($colRrp);
            $qtyRaw           = $get($colQuantity);

            $price = $this->toFloat($priceRaw);
            $rrp   = $this->toFloat($rrpRaw);
            $qty   = $this->toInt($qtyRaw);

            // skip empty lines
            if (!$name && !$supplierSku && !$manufacturerSku) {
                continue;
            }

            PriceRow::create([
                'price_file_id'      => $file->id,
                'supplier_sku'       => $supplierSku,
                'manufacturer_sku'   => $manufacturerSku,
                'manufacturer_name'  => $manufacturerName,
                'barcode'            => $barcode,
                'name'               => $name,
                'category'           => null,
                'price'              => $price,
                'rrp'                => $rrp,
                'quantity'           => $qty,
                'availability_status'=> null,
                'raw'                => $rowArr,
            ]);

            $rowsCount++;
        }

        $file->rows_count = $rowsCount;
        $file->save();

        $this->info("  Imported {$rowsCount} rows.");
    }

    protected function toFloat(?string $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        $value = str_replace([' ', ','], ['', '.'], $value);
        return is_numeric($value) ? (float)$value : null;
    }

    protected function toInt(?string $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        $value = preg_replace('/[^\d\-]/', '', $value);
        return $value !== '' ? (int)$value : null;
    }
}
