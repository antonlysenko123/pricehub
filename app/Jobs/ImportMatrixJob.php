<?php

namespace App\Jobs;

use App\Models\MatrixSync;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImportMatrixJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $syncId;

    public function __construct($syncId)
    {
        $this->syncId = $syncId;
    }

    public function handle(): void
    {
        $sync = MatrixSync::find($this->syncId);
        if (!$sync) return;

        $sync->update([
            'status' => 'running',
            'progress' => 0,
            'current_action' => 'Downloading'
        ]);

        $path = env('MATRIX_FTP_PATH');
        $content = Storage::disk('matrix_ftp')->get($path);

        $lines = explode("\n", $content);
        unset($lines[0]); // header

        $total = count($lines);

        $sync->update([
            'total' => $total,
            'current_action' => 'Importing'
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('supplier_product_matches')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $batchSize = 5000;
        $batch = [];
        $processed = 0;

        foreach ($lines as $line) {

            if (trim($line) === '') continue;

            $row = str_getcsv($line, ';');

            $batch[] = [
                'product_id'       => $row[0] ?? null,
                'product_name'     => $row[11] ?? null,
                'supplier_name'    => $row[4] ?? null,
                'supplier_sku'     => $row[5] ?? null,
                'current_price'    => null,
                'current_quantity' => null,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];

            if (count($batch) >= $batchSize) {

                DB::table('supplier_product_matches')->insert($batch);

                $processed += count($batch);
                $batch = [];

                $sync->update([
                    'processed' => $processed,
                    'progress' => round(($processed / $total) * 100),
                ]);
            }
        }

        // залишок
        if (!empty($batch)) {
            DB::table('supplier_product_matches')->insert($batch);
            $processed += count($batch);
        }

        $sync->update([
            'processed' => $processed,
            'progress' => 100,
            'status' => 'done',
            'current_action' => 'Done'
        ]);
    }
}