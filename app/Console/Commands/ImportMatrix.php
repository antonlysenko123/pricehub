<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImportMatrix extends Command
{
    protected $signature = 'app:import-matrix';
    protected $description = 'Import full matrix from FTP into supplier_product_matches';

    public function handle(): int
    {
        $this->info('Downloading matrix from FTP...');

        try {

            $path = env('MATRIX_FTP_PATH');

            if (!$path) {
                $this->error('MATRIX_FTP_PATH is empty in .env');
                return 1;
            }

            $content = Storage::disk('matrix_ftp')->get($path);

            if (!$content) {
                $this->error('Matrix file is empty');
                return 1;
            }

            // ìàòğèöÿ ïğèõîäèòü â cp1251
            $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1251');

        } catch (\Throwable $e) {

            $this->error('FTP error: ' . $e->getMessage());
            return 1;

        }

        $lines = preg_split("/\r\n|\n|\r/", $content);

        if (!$lines || count($lines) < 2) {
            $this->error('Matrix file has no data rows');
            return 1;
        }

        $this->info('Matrix downloaded. Lines: ' . count($lines));

        $header = str_getcsv($lines[0], ';');
        $this->info('Header columns: ' . count($header));

        // ïîâí³ñòş î÷èùàºìî ìàòğèöş
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('supplier_product_matches')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $batch = [];
        $batchSize = 5000;

        $inserted = 0;
        $skipped = 0;

        foreach ($lines as $index => $line) {

            if ($index === 0) {
                continue;
            }

            if (trim($line) === '') {
                continue;
            }

            $row = str_getcsv($line, ';');

            if (!is_array($row) || count($row) < 12) {
                $skipped++;
                continue;
            }

            $productId = trim($row[0] ?? '');
            $productName = trim($row[11] ?? '');
            $supplierName = trim($row[4] ?? '');
            $supplierSku = trim($row[5] ?? '');

            if ($productName === '') {
                $productName = trim($row[3] ?? '');
            }

            // Ö²ÍÀ Ç ÌÀÒĞÈÖ²
            $price = trim($row[6] ?? '');

            // Ê²ËÜÊ²ÑÒÜ
            $qty = trim($row[7] ?? '');

            // ÍÀßÂÍ²ÑÒÜ
            $availability = trim($row[8] ?? '');

            if ($supplierName === '' && $supplierSku === '' && $productId === '') {
                $skipped++;
                continue;
            }

            $batch[] = [

                'product_id' => $productId !== '' ? (int)$productId : null,
                'product_name' => $productName ?: null,

                'supplier_name' => $supplierName ?: null,
                'supplier_sku' => $supplierSku ?: null,

                'current_price' => $price !== '' ? (float)$price : null,
                'current_quantity' => $qty !== '' ? (int)$qty : null,
                'current_availability' => $availability ?: null,

                'price_row_id' => null,
                'last_synced_at' => null,

                'created_at' => now(),
                'updated_at' => now(),

            ];

            if (count($batch) >= $batchSize) {

                DB::table('supplier_product_matches')->insert($batch);

                $inserted += count($batch);
                $batch = [];

                $this->info("Inserted: {$inserted}");
            }
        }

        if (!empty($batch)) {

            DB::table('supplier_product_matches')->insert($batch);
            $inserted += count($batch);

        }

        $this->info("Matrix import completed. Inserted: {$inserted}; Skipped: {$skipped}");

        return 0;
    }
}