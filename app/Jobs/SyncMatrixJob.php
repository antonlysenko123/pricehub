<?php

namespace App\Jobs;

use App\Models\MatrixSync;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class SyncMatrixJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $syncId; // без типізації

    public function __construct($syncId)
    {
        $this->syncId = $syncId;
    }

    public function handle(): void
    {
        $sync = MatrixSync::find($this->syncId);
        if (!$sync) return;

        $total = DB::table('supplier_product_matches')->count();

        $sync->update([
            'status' => 'running',
            'total' => $total,
            'processed' => 0,
            'progress' => 0,
            'current_action' => 'Updating matrix',
        ]);

        DB::statement("
            UPDATE supplier_product_matches spm
            LEFT JOIN (
                SELECT 
                    pr.id,
                    pr.price,
                    pr.quantity,
                    pr.availability_status,
                    pr.supplier_sku,
                    s.name as supplier_name
                FROM price_rows pr
                JOIN price_files pf ON pr.price_file_id = pf.id
                JOIN suppliers s ON pf.supplier_id = s.id
            ) latest
            ON latest.supplier_name = spm.supplier_name
            AND latest.supplier_sku = spm.supplier_sku
            SET 
                spm.current_price = latest.price,
                spm.current_quantity = latest.quantity,
                spm.current_availability = latest.availability_status,
                spm.price_row_id = latest.id,
                spm.last_synced_at = NOW()
        ");

        $sync->update([
            'processed' => $total,
            'progress' => 100,
            'status' => 'done',
            'current_action' => 'Done',
        ]);
    }
}