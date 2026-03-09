<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BuildProductPrices extends Command
{
    protected $signature = 'app:build-product-prices';
    protected $description = 'Build supplier price stack for products';

    public function handle(): int
    {
        $this->info('Rebuilding product price stack...');

        // ?? Повністю очищаємо стак
        DB::table('product_prices')->truncate();
        $this->info('Old product_prices cleared.');

        $batchSize = 2000;
        $offset = 0;
        $processed = 0;

        while (true) {

            $rows = DB::table('price_rows')
                ->join('price_files','price_rows.price_file_id','=','price_files.id')
                ->join('suppliers','price_files.supplier_id','=','suppliers.id')
                ->leftJoin('supplier_product_matches', function($join){
                    $join->on('supplier_product_matches.supplier_name','=','suppliers.name')
                         ->on('supplier_product_matches.supplier_sku','=','price_rows.supplier_sku');
                })
                ->select(
    'price_rows.id as price_row_id',
    'supplier_product_matches.product_id',
    'suppliers.name as supplier_name',
    'price_rows.supplier_sku',
    'price_rows.name as product_name',
    'price_rows.price',
    'price_rows.quantity',
    'price_rows.availability_status'
)
                
                ->offset($offset)
                ->limit($batchSize)
                ->get();

            if ($rows->isEmpty()) {
                break;
            }

            $insertBatch = [];

            foreach ($rows as $row) {

                $insertBatch[] = [
                    'product_id'        => $row->product_id,
'supplier_name'     => $row->supplier_name,
'supplier_sku'      => $row->supplier_sku,
'product_name'      => $row->product_name,
'price'             => $row->price,
                    'quantity'          => $row->quantity,
                    'availability_status'=> $row->availability_status,
                    'price_row_id'      => $row->price_row_id,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];
            }

            DB::table('product_prices')->upsert(
    $insertBatch,
    ['supplier_name', 'supplier_sku'],
    ['product_id','product_name','price','quantity','availability_status','price_row_id','updated_at']
);

            $processed += count($insertBatch);
            $this->info("Processed: {$processed}");

            $offset += $batchSize;
        }

        $this->info("Product price stack built. Total rows: {$processed}");

        return 0;
    }
}