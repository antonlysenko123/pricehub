<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncMatrixPrices extends Command
{
    protected $signature = 'app:sync-matrix-prices';
    protected $description = 'Sync matrix with latest supplier prices';

    public function handle()
    {
        $this->info('Starting matrix sync...');

        /*
        |--------------------------------------------------------------------------
        | 1. Видаляємо старі рядки з прайсів
        |--------------------------------------------------------------------------
        */

        $this->info('Cleaning temporary rows...');

        DB::table('supplier_product_matches')
            ->whereNull('product_id')
            ->delete();


        /*
        |--------------------------------------------------------------------------
        | 2. Створюємо тимчасову таблицю останніх прайсів
        |--------------------------------------------------------------------------
        */

        $this->info('Preparing latest prices...');

        DB::statement("
            CREATE TEMPORARY TABLE tmp_latest_prices AS

            SELECT
                s.name as supplier_name,
                COALESCE(NULLIF(pr.supplier_sku,''), pr.name) as supplier_sku,
                pr.name as product_name,
                pr.price,
                pr.quantity,
                pr.availability_status,
                pr.id as price_row_id

            FROM price_rows pr

            JOIN (
                SELECT supplier_id, MAX(id) as last_file
                FROM price_files
                GROUP BY supplier_id
            ) latest_pf
                ON pr.price_file_id = latest_pf.last_file

            JOIN price_files pf
                ON pf.id = latest_pf.last_file

            JOIN suppliers s
                ON s.id = pf.supplier_id
        ");

      DB::statement("
    CREATE INDEX idx_tmp_supplier_sku
    ON tmp_latest_prices (supplier_name(200), supplier_sku(200))
");


        /*
        |--------------------------------------------------------------------------
        | 3. Оновлюємо ціни у вже існуючій матриці
        |--------------------------------------------------------------------------
        */

        $this->info('Updating matrix prices...');

        DB::statement("
            UPDATE supplier_product_matches spm

            JOIN tmp_latest_prices t
                ON t.supplier_name = spm.supplier_name
                AND t.supplier_sku = spm.supplier_sku

            SET
                spm.current_price = t.price,
                spm.current_quantity = t.quantity,
                spm.current_availability = t.availability_status,
                spm.price_row_id = t.price_row_id,
                spm.last_synced_at = NOW()
        ");


        /*
        |--------------------------------------------------------------------------
        | 4. Додаємо нові SKU з прайсів яких нема в матриці
        |--------------------------------------------------------------------------
        */

        $this->info('Appending missing SKUs...');

        DB::statement("
            INSERT IGNORE INTO supplier_product_matches
            (
                product_id,
                product_name,
                supplier_name,
                supplier_sku,
                current_price,
                current_quantity,
                current_availability,
                price_row_id,
                last_synced_at,
                created_at,
                updated_at
            )

            SELECT
                NULL,
                t.product_name,
                t.supplier_name,
                t.supplier_sku,
                t.price,
                t.quantity,
                t.availability_status,
                t.price_row_id,
                NOW(),
                NOW(),
                NOW()

            FROM tmp_latest_prices t

            LEFT JOIN supplier_product_matches spm
                ON spm.supplier_name = t.supplier_name
                AND spm.supplier_sku = t.supplier_sku

            WHERE spm.id IS NULL
        ");


        $this->info('Matrix sync completed.');

        return 0;
    }
}